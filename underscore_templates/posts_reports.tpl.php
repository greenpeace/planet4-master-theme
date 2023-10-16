<?php // phpcs:ignoreFile ?>
<script type="text/template" id="tmpl-p4-post">
    <td class="title column-title column-primary page-title" data-colname="Title">
        <a href="{{{ data.link }}}">{{{ data.title.rendered }}}</a>
    </td>
    <td>{{{ data.status }}}</td>
    <td>{{{ data.date.replace('T', ' ')  }}}</td>
    <td>{{ data.modified.replace('T', ' ') }}</td>
</script>

<script type="text/template" id="tmpl-p4-post-list">
    <table class="wp-list-table widefat">
        <thead>
            <th><?php _e( 'Title', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Publish Status', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Published at', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Modified at', 'planet4-master-theme-backend' ) ?></th>
        </thead>
        <tbody class="p4-posts"></tbody>
    </table>
    <p>
        <button class="button button-primary refresh"><?php _e( 'Refresh', 'planet4-master-theme-backend' ) ?></button>
    </p>
</script>

<script type="text/template" id="tmpl-p4-page-list">
    <table class="wp-list-table widefat">
        <thead>
            <th><?php _e( 'Title', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Publish Status', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Published at', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Modified at', 'planet4-master-theme-backend' ) ?></th>
        </thead>
        <tbody class="p4-pages"></tbody>
    </table>
    <p>
        <button class="button button-primary refresh"><?php _e( 'Refresh', 'planet4-master-theme-backend' ) ?></button>
    </p>
</script>

<script type="text/template" id="tmpl-p4-campaign-list">
    <table class="wp-list-table widefat">
        <thead>
            <th><?php _e( 'Title', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Publish Status', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Published at', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Modified at', 'planet4-master-theme-backend' ) ?></th>
        </thead>
        <tbody class="p4-campaigns"></tbody>
    </table>
    <p>
        <button class="button button-primary refresh"><?php _e( 'Refresh', 'planet4-master-theme-backend' ) ?></button>
    </p>
</script>

<script type="text/template" id="tmpl-p4-action-list">
    <table class="wp-list-table widefat">
        <thead>
            <th><?php _e( 'Title', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Publish Status', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Published at', 'planet4-master-theme-backend' ) ?></th>
            <th><?php _e( 'Modified at', 'planet4-master-theme-backend' ) ?></th>
        </thead>
        <tbody class="p4-actions"></tbody>
    </table>
    <p>
        <button class="button button-primary refresh"><?php _e( 'Refresh', 'planet4-master-theme-backend' ) ?></button>
    </p>
</script>
