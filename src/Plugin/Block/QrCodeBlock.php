<?php

namespace Drupal\products\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use \Drupal\file\Entity\File;
use Drupal\Core\Cache\Cache;
/**
 * Provides a block with a QRcode.
 *
 * @Block(
 *   id = "qrcode_block",
 *   admin_label = @Translation("QRcode block"),
 * )
 */
class QrCodeBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
    public function build() {
        global $base_url;
        $node = \Drupal::routeMatch()->getParameter('node');
        $nid = $node->id();
        $link = $node->field_product_link->uri;
        $title = $node->field_product_title->value;
        $title_filter = str_replace(' ','',$title); 
        if(!empty($link)){
            $image = self::generateQrCodes($link,$title_filter);
                $image_src = $base_url.'/sites/default/files/images/QrCodes/'.$title_filter.'.png';
        }
        else{
            $image_src = '';
        }
        return [
            '#items' => $image_src,
            '#theme' => 'qr_code',
        ];
    }

    public function generateQrCodes($qr_text,$title) {
        $path = '';
        $directory = "public://images/QrCodes/";
        // deprecated file_prepare_directory($directory, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY);
        \Drupal::service('file_system')->prepareDirectory($directory, FILE_MODIFY_PERMISSIONS | FILE_CREATE_DIRECTORY);
        // Name of the generated image.
        $uri = $directory . $title . '.png'; // Generates a png image.
        // deprecated $path = drupal_realpath($uri);
        $path = \Drupal::service('file_system')->realpath($uri);
        // Generate QR code image.
        \PHPQRCode\QRcode::png($qr_text, $path);
        return $path;
    }
    public function getCacheTags(){
        //With this when your node change your block will rebuild
        if ($node = \Drupal::routeMatch()->getParameter('node')) {
            //if there is node add its cachetag
            return Cache::mergeTags(parent::getCacheTags(), [
            'node:' . $node->id(),
            ]);
        } else {
            //Return default tags instead.
            return parent::getCacheTags();
        }
    }

    public function getCacheContexts(){
        //if you depends on \Drupal::routeMatch()
        //you must set context of this block with 'route' context tag.
        //Every new route this block will rebuild
        return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
    }

}