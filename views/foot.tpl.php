
    <?php $view->load_js("../layui.all.js"); ?>
    <?php $view->load_js("main.js"); ?>

    <?php if(isset($ErrInfo)){?>
        <script type="text/javascript">
            layer.msg('<?php echo $ErrInfo;?>', {icon: 2});
        </script>
    <?php } ?>


    <?php $view->google_analytics(); ?>
    </body>
</html>