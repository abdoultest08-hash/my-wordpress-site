<?php
$stats = [
    [ 'number' => '200', 'suffix' => '+', 'label' => __( 'Service Users Supported', 'mybrand' ) ],
    [ 'number' => '10',  'suffix' => '+', 'label' => __( 'Years of Experience',     'mybrand' ) ],
    [ 'number' => '98',  'suffix' => '%', 'label' => __( 'Satisfaction Rate',        'mybrand' ) ],
    [ 'number' => '24',  'suffix' => '/7','label' => __( 'Support Available',        'mybrand' ) ],
];
?>
<div class="stats-band" aria-label="<?php esc_attr_e( 'Key statistics', 'mybrand' ); ?>">
    <div class="container">
        <div class="stats-grid">
            <?php foreach ( $stats as $stat ) : ?>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html( $stat['number'] ); ?><span><?php echo esc_html( $stat['suffix'] ); ?></span></span>
                    <span class="stat-label"><?php echo esc_html( $stat['label'] ); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
