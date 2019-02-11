<?php
//防止被恶意访问，泄露信息
if(!defined('DEBUG')) {
    http_response_code(403);
    exit('Access Denied');
}
?>
    <?php if(isset($ErrInfo)){?>
        <script type="text/javascript">
            layer.msg('<?php echo $ErrInfo;?>', {icon: 2});
        </script>
    <?php } ?>


    <?php $view->google_analytics(); ?>
    </body>
</html>