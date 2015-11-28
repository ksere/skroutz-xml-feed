<?php
/**
 * Project: skroutz.gr-xml-feed
 * File: info_panel.php
 * User: Panagiotis Vagenas <pan.vagenas@gmail.com>
 * Date: 23/10/2014
 * Time: 11:04 μμ
 * Since: 141017
 * Copyright: 2014 Panagiotis Vagenas
 */
if ( ! defined( 'WPINC' ) ) {
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
}

/* @var \skroutz\menu_pages\panels\log $callee */
/* @var \xd_v141226_dev\views $this */

$this->©diagnostic->loadDBLog();
$messages = $this->©diagnostic->get_messages( 'product' );

//var_dump( $this->©diagnostic->get_messages_as_markup('product'));
if ( empty( $messages ) ) {
    echo '<p>Log is empty</p>';

    return;
}
?>
<div class="row">
    <div class="col-md-12 b-margin">
        <div class="btn-group btn-group-justified" role="group">
            <a class="btn btn-md btn-danger log-filter active" href="#" data-filter="danger">Errors</a>
            <a class="btn btn-md btn-warning log-filter active" href="#" data-filter="warning">Warnings</a>
            <a class="btn btn-md btn-success log-filter active" href="#" data-filter="success">Info</a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12 b-margin">
        <?php
        $ids = array();
        foreach ( $messages as $k => $message ) {
            switch ( $message['type'] ) {
                case 'error':
                    $msgClass = 'alert alert-danger';
                    break;
                case 'message':
                    $msgClass = 'alert alert-warning';
                    break;
                case 'success':
                    $msgClass = 'alert alert-success';
                    break;
                default:
                case 'diagnostic':
                    $msgClass = 'alert alert-info';
                    break;
            }
            ?>
            <div class="<?php echo $msgClass; ?> log-alerts">
                <?php echo $message['msg']; ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>

