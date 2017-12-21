<?php
/**
 * Booster for WooCommerce - PDF Invoicing - Email Options
 *
 * @version 3.1.1
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WCJ_PDF_Invoicing_Emails' ) ) :

class WCJ_PDF_Invoicing_Emails extends WCJ_Module {

	/**
	 * Constructor.
	 *
	 * @version 2.8.0
	 */
	function __construct() {

		$this->id         = 'pdf_invoicing_emails';
		$this->parent_id  = 'pdf_invoicing';
		$this->short_desc = __( 'Email Options', 'woocommerce-jetpack' );
		$this->desc       = '';
		parent::__construct( 'submodule' );

		if ( $this->is_enabled() ) {
			if ( ! wcj_is_module_enabled( 'general' ) || 'no' === get_option( 'wcj_general_advanced_disable_save_sys_temp_dir', 'no' ) ) {
				add_filter( 'woocommerce_email_attachments', array( $this, 'add_pdf_invoice_email_attachment' ), PHP_INT_MAX, 3 );
			}
		}
	}

	/**
	 * do_attach_for_payment_method.
	 *
	 * @version 2.8.0
	 */
	function do_attach_for_payment_method( $invoice_type_id, $payment_method ) {
		$included_gateways = get_option( 'wcj_invoicing_' . $invoice_type_id . '_payment_gateways', array() );
		if ( empty ( $included_gateways ) ) {
			return true; // include all
		}
		return ( in_array( $payment_method, $included_gateways ) );
	}

	/**
	 * add_pdf_invoice_email_attachment.
	 *
	 * @version 3.1.1
	 */
	function add_pdf_invoice_email_attachment( $attachments, $status, $order ) {
		if ( ! $order || ! is_object( $order ) ) {
			return $attachments;
		}
		$invoice_types_ids = wcj_get_enabled_invoice_types_ids();
		foreach ( $invoice_types_ids as $invoice_type_id ) {
			if ( false === $this->do_attach_for_payment_method( $invoice_type_id, wcj_order_get_payment_method( $order ) ) ) {
				continue;
			}
			$send_on_statuses = get_option( 'wcj_invoicing_' . $invoice_type_id . '_attach_to_emails', array() );
			if ( '' == $send_on_statuses ) {
				$send_on_statuses = array();
			}
			if ( in_array( $status, $send_on_statuses ) ) {
				$the_invoice = wcj_get_pdf_invoice( wcj_get_order_id( $order ), $invoice_type_id );
				$file_name   = $the_invoice->get_pdf( 'F' );
				if ( '' != $file_name ) {
					$attachments[] = $file_name;
				}
			}
		}
		return $attachments;
	}

}

endif;

return new WCJ_PDF_Invoicing_Emails();