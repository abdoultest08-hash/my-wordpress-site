<?php
$stats = [
    [ 'number' => '40',  'suffix' => '+',  'label' => 'Members of Staff' ],
    [ 'number' => '3',   'suffix' => ':1', 'label' => 'Complex Packages Delivered' ],
    [ 'number' => 'Good','suffix' => '',   'label' => 'CQC Rating — June 2025' ],
    [ 'number' => '24',  'suffix' => '/7', 'label' => 'Support Available' ],
];
?>
<div class="stats-band" aria-label="Key statistics">
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
