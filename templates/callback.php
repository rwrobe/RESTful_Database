<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title></title>
    <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.1.min.js"></script>
    <script type="text/javascript">
        /* <![CDATA[ */
        var rdb_options = {
            "site_id": "<?php echo get_current_blog_id(); ?>"
        };
        /* ]]> */
    </script>
    <script src="<?php echo esc_url( RDB_PLUGIN_URL . '/js/OD_Auth.js' ); ?>"></script>
</head>
<body>
<script>
    OD_Auth.onAuthCallback();
</script>
</body>
</html>