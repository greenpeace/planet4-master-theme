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
        <th>Title</th>
        <th>Publish Status</th>
        <th>Published at</th>
        <th>Modified at</th>
        </thead>
        <tbody class="p4-posts"></tbody>
    </table>
    <p>
        <button class="button button-primary refresh">Refresh</button>
    </p>
</script>

<script type="text/template" id="tmpl-p4-page-list">
    <table class="wp-list-table widefat">
        <thead>
        <th>Title</th>
        <th>Publish Status</th>
        <th>Published at</th>
        <th>Modified at</th>
        </thead>
        <tbody class="p4-pages"></tbody>
    </table>
    <p>
        <button class="button button-primary refresh">Refresh</button>
    </p>
</script>


