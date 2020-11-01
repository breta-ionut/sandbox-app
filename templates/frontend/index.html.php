<?php /** @var \Symfony\Component\Templating\PhpEngine $view */ ?>

<?php $view->extend('base.html.php'); ?>

<?php $view['slots']->set('html_classes', 'bg-gray-100'); ?>

<?php $view['slots']->set('title', 'Sandbox application'); ?>

<?php $view['slots']->start('body'); ?>
    <div id="app"></div>
<?php $view['slots']->stop(); ?>

<?php $view['slots']->start('javascripts'); ?>
    <script src="<?= $view['assets']->getUrl('index.js'); ?>"></script>
<?php $view['slots']->stop(); ?>
