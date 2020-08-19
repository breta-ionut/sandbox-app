<?php /** @var \Symfony\Component\Templating\PhpEngine $view */ ?>
<?php /** @var string $api_doc_config_url */ ?>

<?php $view->extend('base.html.php'); ?>

<?php $view['slots']->set('title', 'Sandbox API documentation'); ?>

<?php $view['slots']->start('body'); ?>
    <div id="swagger"></div>
<?php $view['slots']->stop(); ?>

<?php $view['slots']->start('javascripts'); ?>
    <script>
        window.apiDocConfigUrl = '<?= $api_doc_config_url ?>'
    </script>

    <script src="<?= $view['assets']->getUrl('api_doc.js', 'api_doc'); ?>"></script>
<?php $view['slots']->stop(); ?>
