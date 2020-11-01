<?php /** @var \Symfony\Component\Templating\PhpEngine $view */ ?>

<!DOCTYPE html>
<html lang="en" class="<?php $view['slots']->output('html_classes'); ?>">
    <head>
        <meta charset="UTF-8" />
        <title><?php $view['slots']->output('title'); ?></title>

        <?php $view['slots']->output('stylesheets'); ?>
    </head>
    <body class="<?php $view['slots']->output('body_classes'); ?>">
        <?php $view['slots']->output('body'); ?>

        <?php $view['slots']->output('javascripts'); ?>
    </body>
</html>
