<!DOCTYPE html>
<html lang="en-US">
<head>
    <?php
    $this->load->view("components/page_header");
    $userType = $this->session->userdata('user_type');
    $returnPrefix = '';
    if ($userType == '2')
        $returnPrefix = 'student/index/';
    ?>
    <script src="<?= base_url('assets/js/frontend/global.js') ?>"></script>
</head>
<body>
<div>

    <script>
        var imageDir = baseURL + "";
        var loginUserType = '<?=$userType?>';
    </script>

    <?php $this->load->view($subview); ?>

    <?php $this->load->view("components/page_menu"); ?>

    <?php $this->load->view("components/resource_toolbar"); ?>

    <?php $this->load->view("components/page_footer"); ?>

</div>
</body>
</html>
