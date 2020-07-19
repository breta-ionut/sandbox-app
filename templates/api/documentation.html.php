<?php /** @var \Symfony\Component\Templating\PhpEngine $view */ ?>

<?php $view->extend('base.html.php'); ?>

<?php $view['slots']->set('title', 'Sandbox API documentation'); ?>

<?php $view['slots']->start('body'); ?>
    <div id="swagger"></div>
<?php $view['slots']->stop(); ?>

<?php $view['slots']->start('javascripts'); ?>
    <script src="<?= $view['assets']->getUrl('swagger.js'); ?>"></script>
<?php $view['slots']->stop(); ?>
