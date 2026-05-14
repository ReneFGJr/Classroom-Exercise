<?= $this->include('layout/header') ?>
<?= $this->include('layout/navbar') ?>

<main class="container py-4">
    <?= $this->renderSection('content') ?>
</main>

<?= $this->include('layout/footer') ?>
