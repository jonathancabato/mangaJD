<?php
use ActiveTools\Utility\Logger;

    $log_levels_options = [
        'debug' => [
            'label' => Logger::get_level_label( Logger::LEVEL_DEBUG ),
            'description' => Logger::get_level_description( Logger::LEVEL_DEBUG ),
            'value' => Logger::LEVEL_DEBUG,
        ],
        'notice' => [
            'label' => Logger::get_level_label( Logger::LEVEL_NOTICE ),
            'description' => Logger::get_level_description( Logger::LEVEL_NOTICE ),
            'value' => Logger::LEVEL_NOTICE,
        ],
        'warning' => [
            'label' => Logger::get_level_label( Logger::LEVEL_WARNING ),
            'description' => Logger::get_level_description( Logger::LEVEL_WARNING ),
            'value' => Logger::LEVEL_WARNING,
        ],
        'error' => [
            'label' => Logger::get_level_label( Logger::LEVEL_ERROR ),
            'description' => Logger::get_level_description( Logger::LEVEL_ERROR ),
            'value' => Logger::LEVEL_ERROR,
        ],
    ];

?>
<div class="at-log-filter-options">
    <div class="filter-options">
        <div class="filter-by-level">
            <h3>Filter by Log Level:</h3>
            <div>
            <?php foreach ( $log_levels_options as $slug => $option ) : ?>
                <label for="filter-logs-show-<?php esc_attr_e( $slug ); ?>">
                    <input type="checkbox"
                           name="filter-logs-show-<?php esc_attr_e( $slug ); ?>"
                           id="filter-logs-show-<?php esc_attr_e( $slug ); ?>"
                           value="<?php esc_attr_e( $option['value'] ); ?>"
                    >
                    <?php esc_html_e( $option['label'] ); ?>
                </label>
            <?php endforeach; ?>
            </div>
        </div>
        <div class="filter-by-date">
            <h3>Filter by Date</h3>
            <label for="filter-logs-date-from">
                From:
                <input type="date" name="filter-logs-date-from" id="filter-logs-date-from">
            </label>
            <label for="filter-logs-date-to">
                To:
                <input type="datetime-local" name="filter-logs-date-to" id="filter-logs-date-to">
            </label>
        </div>
    </div>
</div>
