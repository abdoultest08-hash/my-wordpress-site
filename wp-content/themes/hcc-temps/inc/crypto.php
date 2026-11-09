<?php
/**
 * Encryption at rest for candidate documents (wp-content/uploads/hcc-private/...).
 *
 * Every uploaded file is encrypted on the server as soon as it arrives (libsodium secretstream,
 * XChaCha20-Poly1305, in 64 KB chunks). On disk a file is unreadable without the key, so a copied
 * file, a guessed link or a downloaded backup shows nothing. Staff and the candidate still see
 * the normal file: it is decrypted only while it is being sent to a logged-in, allowed person.
 *
 * The key: the constant HCC_FILE_KEY in wp-config.php if it is defined (best), otherwise a random
 * key the theme creates once and keeps in the database. Losing the key means losing every file,
 * so Users → File security shows where it is and how to keep a copy.
 *
 * File layout: "HCCE1" | 8-byte key fingerprint | 24-byte stream header | chunks (each = plain + 17 bytes).
 * Files written before encryption was added are recognised (no "HCCE1" prefix), still served, and
 * encrypted in the background a batch at a time.
 */

defined( 'ABSPATH' ) || exit;

const HCC_CRYPTO_MAGIC = 'HCCE1';
const HCC_CRYPTO_CHUNK = 65536;

function hcc_crypto_available() {
	return function_exists( 'sodium_crypto_secretstream_xchacha20poly1305_init_push' );
}

/** Where the key comes from: 'config' (wp-config.php) or 'database'. */
function hcc_crypto_key_source() {
	return ( defined( 'HCC_FILE_KEY' ) && '' !== (string) HCC_FILE_KEY ) ? 'config' : 'database';
}

/** The stored form of the database key (base64), created once. */
function hcc_crypto_db_key() {
	$k = get_option( 'hcc_file_key' );
	if ( ! $k ) {
		$k = base64_encode( random_bytes( 32 ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
		add_option( 'hcc_file_key', $k, '', 'no' );
		$k = get_option( 'hcc_file_key' ); // another request may have won the race
	}
	return (string) $k;
}

/** 32-byte key. */
function hcc_crypto_key() {
	static $key = null;
	if ( null === $key ) {
		$raw = 'config' === hcc_crypto_key_source() ? (string) HCC_FILE_KEY : hcc_crypto_db_key();
		$key = sodium_crypto_generichash( 'hcc-file-key|' . $raw, '', SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_KEYBYTES );
	}
	return $key;
}

function hcc_crypto_fingerprint() {
	return substr( sodium_crypto_generichash( 'fp|' . hcc_crypto_key(), '', 16 ), 0, 8 );
}

function hcc_crypto_header_len() {
	return strlen( HCC_CRYPTO_MAGIC ) + 8 + SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES;
}

function hcc_is_encrypted( $path ) {
	$h = @fopen( $path, 'rb' ); // phpcs:ignore
	if ( ! $h ) {
		return false;
	}
	$m = fread( $h, strlen( HCC_CRYPTO_MAGIC ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fclose( $h ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return HCC_CRYPTO_MAGIC === $m;
}

/** False only for an encrypted file whose key is not the current one. */
function hcc_crypto_can_open( $path ) {
	if ( ! hcc_is_encrypted( $path ) ) {
		return true;
	}
	if ( ! hcc_crypto_available() ) {
		return false;
	}
	$h = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fseek( $h, strlen( HCC_CRYPTO_MAGIC ) );
	$fp = fread( $h, 8 ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fclose( $h ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return $fp === hcc_crypto_fingerprint();
}

/** Size of the original file (what the browser receives). */
function hcc_plain_size( $path ) {
	$size = (int) filesize( $path );
	if ( ! hcc_is_encrypted( $path ) ) {
		return $size;
	}
	$body   = $size - hcc_crypto_header_len();
	$chunks = max( 1, (int) ceil( $body / ( HCC_CRYPTO_CHUNK + SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES ) ) );
	return max( 0, $body - $chunks * SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES );
}

/** Encrypt a file in place. Already-encrypted files are left alone. */
function hcc_encrypt_file( $path ) {
	if ( ! hcc_crypto_available() || ! is_file( $path ) || hcc_is_encrypted( $path ) ) {
		return false;
	}
	$tmp = $path . '.enc-' . wp_generate_password( 6, false );
	$in  = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$out = fopen( $tmp, 'wb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! $in || ! $out ) {
		return false;
	}
	list( $state, $header ) = sodium_crypto_secretstream_xchacha20poly1305_init_push( hcc_crypto_key() );
	fwrite( $out, HCC_CRYPTO_MAGIC . hcc_crypto_fingerprint() . $header ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	$buf = (string) fread( $in, HCC_CRYPTO_CHUNK ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	do {
		$next = feof( $in ) ? '' : (string) fread( $in, HCC_CRYPTO_CHUNK ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$last = '' === $next;
		fwrite( $out, sodium_crypto_secretstream_xchacha20poly1305_push( $state, $buf, '', $last ? SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL : SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_MESSAGE ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$buf = $next;
	} while ( ! $last );
	fclose( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	// Check the new file opens with the key before replacing the original.
	$check = wp_tempnam( 'hcc-check' );
	$ok    = hcc_decrypt_file( $tmp, $check ) && filesize( $check ) === filesize( $path ) && hash_file( 'sha256', $check ) === hash_file( 'sha256', $path );
	@unlink( $check ); // phpcs:ignore
	if ( ! $ok || ! @rename( $tmp, $path ) ) { // phpcs:ignore
		@unlink( $tmp ); // phpcs:ignore
		return false;
	}
	return true;
}

/**
 * Decrypt $path, sending each piece to $sink (a callable, or a file path to write to).
 * Plain (not yet encrypted) files are passed through unchanged. Returns false if the file is
 * damaged or was encrypted with a different key.
 */
function hcc_decrypt_file( $path, $sink ) {
	$write = is_callable( $sink ) ? $sink : null;
	$out   = null;
	if ( ! $write ) {
		$out   = fopen( $sink, 'wb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$write = function ( $s ) use ( $out ) {
			fwrite( $out, $s ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		};
	}
	$in = fopen( $path, 'rb' ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( ! $in ) {
		return false;
	}
	$ok = true;
	if ( HCC_CRYPTO_MAGIC !== fread( $in, strlen( HCC_CRYPTO_MAGIC ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
		rewind( $in );
		while ( ! feof( $in ) ) {
			$write( (string) fread( $in, HCC_CRYPTO_CHUNK ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	} elseif ( ! hcc_crypto_available() || fread( $in, 8 ) !== hcc_crypto_fingerprint() ) { // phpcs:ignore WordPress.WP.AlternativeFunctions
		$ok = false; // different key
	} else {
		$state = sodium_crypto_secretstream_xchacha20poly1305_init_pull( fread( $in, SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_HEADERBYTES ), hcc_crypto_key() ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		$ok    = false;
		while ( ! feof( $in ) ) {
			$c = (string) fread( $in, HCC_CRYPTO_CHUNK + SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_ABYTES ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( '' === $c ) {
				break;
			}
			$r = sodium_crypto_secretstream_xchacha20poly1305_pull( $state, $c );
			if ( false === $r ) {
				break; // tampered or damaged
			}
			$write( $r[0] );
			if ( SODIUM_CRYPTO_SECRETSTREAM_XCHACHA20POLY1305_TAG_FINAL === $r[1] ) {
				$ok = true;
				break;
			}
		}
	}
	fclose( $in ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	if ( $out ) {
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	return $ok;
}

/**
 * A decrypted copy in a private temporary folder, keeping the original file name (for email
 * attachments and zips). Delete it with hcc_crypto_cleanup() when done.
 */
function hcc_decrypted_copy( $path ) {
	$dir = trailingslashit( get_temp_dir() ) . 'hcc-' . wp_generate_password( 16, false );
	if ( ! wp_mkdir_p( $dir ) ) {
		return '';
	}
	$copy = $dir . '/' . basename( $path );
	if ( ! hcc_decrypt_file( $path, $copy ) ) {
		hcc_crypto_cleanup( $copy );
		return '';
	}
	return $copy;
}

function hcc_crypto_cleanup( $copies ) {
	foreach ( (array) $copies as $c ) {
		if ( $c && false !== strpos( $c, '/hcc-' ) ) {
			@unlink( $c ); // phpcs:ignore
			@rmdir( dirname( $c ) ); // phpcs:ignore
		}
	}
}

/* ---------- Encrypting files saved before this was added ---------- */

/** Every candidate file on disk, with whether it is encrypted. */
function hcc_crypto_files() {
	$up    = wp_upload_dir();
	$base  = trailingslashit( $up['basedir'] ) . 'hcc-private';
	$files = array();
	foreach ( (array) glob( $base . '/*', GLOB_ONLYDIR ) as $dir ) {
		foreach ( (array) glob( $dir . '/*' ) as $f ) {
			if ( is_file( $f ) && 'index.php' !== basename( $f ) && false === strpos( basename( $f ), '.enc-' ) ) {
				$files[] = $f;
			}
		}
	}
	return $files;
}

function hcc_crypto_status() {
	$n = 0;
	$e = 0;
	foreach ( hcc_crypto_files() as $f ) {
		$n++;
		$e += hcc_is_encrypted( $f ) ? 1 : 0;
	}
	return array( 'total' => $n, 'encrypted' => $e, 'plain' => $n - $e );
}

/** Encrypt up to $limit plain files. Returns how many were done. */
function hcc_crypto_migrate( $limit = 200 ) {
	$done = 0;
	foreach ( hcc_crypto_files() as $f ) {
		if ( $done >= $limit ) {
			break;
		}
		if ( ! hcc_is_encrypted( $f ) && hcc_encrypt_file( $f ) ) {
			$done++;
		}
	}
	return $done;
}

/* Background: each hourly run encrypts a batch until nothing is left. */
add_action( 'hcc_daily', function () {
	if ( hcc_crypto_available() && get_option( 'hcc_crypto_done' ) !== hcc_crypto_key_source() . '1' ) {
		hcc_crypto_migrate( 300 );
		if ( 0 === hcc_crypto_status()['plain'] ) {
			update_option( 'hcc_crypto_done', hcc_crypto_key_source() . '1', false );
		}
	}
}, 20 );

/* ---------- Users → File security (administrators) ---------- */

add_action( 'admin_menu', function () {
	add_users_page( 'File security', 'File security', 'manage_options', 'hcc-file-security', 'hcc_crypto_page' );
} );

function hcc_crypto_page() {
	$s = hcc_crypto_status();
	echo '<div class="wrap"><h1>File security</h1>';
	if ( isset( $_GET['done'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		printf( '<div class="notice notice-success"><p>%d files encrypted.</p></div>', (int) $_GET['done'] ); // phpcs:ignore
	}
	if ( ! hcc_crypto_available() ) {
		echo '<div class="notice notice-error"><p>This server does not have the encryption library (libsodium). Files are stored without encryption. Ask your host to enable the PHP sodium extension.</p></div></div>';
		return;
	}
	echo '<p>Candidate documents are encrypted on the server. They are only decrypted while a logged-in member of staff, or the candidate themselves, opens them.</p>';
	echo '<table class="widefat striped" style="max-width:720px"><tbody>';
	printf( '<tr><th style="width:40%%">Files</th><td>%d in total: <b>%d encrypted</b>%s</td></tr>', (int) $s['total'], (int) $s['encrypted'], $s['plain'] ? ', <b style="color:#B3261E">' . (int) $s['plain'] . ' not encrypted yet</b> (done automatically within a few hours, or use the button below)' : '' );
	printf( '<tr><th>Where the key is kept</th><td>%s</td></tr>', 'config' === hcc_crypto_key_source() ? '<b style="color:#17694A">wp-config.php</b> (recommended)' : '<b>The website database</b>. It works, but a copy of the database would include the key. Moving it to wp-config.php is safer (see below).' );
	echo '</tbody></table>';
	if ( $s['plain'] ) {
		echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-top:14px"><input type="hidden" name="action" value="hcc_crypto_now">';
		wp_nonce_field( 'hcc_crypto_now' );
		echo '<button class="button button-primary">Encrypt the remaining files now</button></form>';
	}
	echo '<h2 style="margin-top:28px">Keep a copy of the key</h2><p><b>Without the key, the files can never be opened again</b>, even from a backup. Store a copy somewhere safe and separate from the website, for example in the company password manager.</p>';
	if ( 'database' === hcc_crypto_key_source() ) {
		echo '<details style="max-width:720px"><summary style="cursor:pointer"><b>Show the key and how to move it to wp-config.php</b></summary>';
		echo '<p>1. Copy this line into <code>wp-config.php</code>, above the line <code>/* That\'s all, stop editing! */</code>. On WordPress.com you can edit that file over SFTP (Settings → Hosting Configuration).</p>';
		echo '<p><input type="text" readonly onclick="this.select()" style="width:100%;font-family:monospace" value="' . esc_attr( "define( 'HCC_FILE_KEY', '" . hcc_crypto_db_key() . "' );" ) . '"></p>';
		echo '<p>2. Reload this page. It should say "wp-config.php". Existing files keep working because it is the same key.</p>';
		echo '<p>3. Save the same line in the company password manager.</p></details>';
	} else {
		echo '<p>The key is the <code>HCC_FILE_KEY</code> line in <code>wp-config.php</code>. Never change or remove that line: files encrypted with it could not be opened.</p>';
	}
	echo '</div>';
}

add_action( 'admin_post_hcc_crypto_now', function () {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Not allowed.', 403 );
	}
	check_admin_referer( 'hcc_crypto_now' );
	@set_time_limit( 300 ); // phpcs:ignore
	$n = hcc_crypto_migrate( 1000 );
	wp_safe_redirect( add_query_arg( 'done', $n, admin_url( 'users.php?page=hcc-file-security' ) ) );
	exit;
} );
