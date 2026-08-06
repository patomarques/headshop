<?php
defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	if ( get_option( 'eletronicos_newsletter_db_v1' ) ) return;
	global $wpdb;
	$table   = $wpdb->prefix . 'newsletter_subscribers';
	$charset = $wpdb->get_charset_collate();
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( "CREATE TABLE {$table} (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		email varchar(255) NOT NULL,
		ip_address varchar(45) NOT NULL DEFAULT '',
		subscribed_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		status varchar(20) NOT NULL DEFAULT 'active',
		PRIMARY KEY (id),
		UNIQUE KEY email (email)
	) {$charset};" );
	update_option( 'eletronicos_newsletter_db_v1', true );
} );

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_front_page() ) return;
	$ver = filemtime( get_stylesheet_directory() . '/assets/js/newsletter.js' );
	wp_enqueue_script( 'eletronicos-newsletter', get_stylesheet_directory_uri() . '/assets/js/newsletter.js', [], $ver, true );
	wp_localize_script( 'eletronicos-newsletter', 'newsletterData', [
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'newsletter_nonce' ),
	] );
} );

add_action( 'wp_ajax_nopriv_newsletter_subscribe', 'eletronicos_newsletter_subscribe' );
add_action( 'wp_ajax_newsletter_subscribe', 'eletronicos_newsletter_subscribe' );

function eletronicos_newsletter_subscribe() {
	if ( ! check_ajax_referer( 'newsletter_nonce', 'nonce', false ) ) {
		wp_send_json_error( [ 'message' => 'Requisição inválida.' ] );
	}

	// Honeypot — bots preenchem, humanos não
	if ( ! empty( $_POST['url'] ) ) {
		wp_send_json_success( [ 'message' => 'Cadastro realizado! Obrigado por assinar.' ] );
	}

	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );

	if ( ! is_email( $email ) ) {
		wp_send_json_error( [ 'message' => 'Por favor, informe um email válido.' ] );
	}

	$ip       = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$rate_key = 'nl_rate_' . md5( $ip );
	$attempts = (int) get_transient( $rate_key );

	if ( $attempts >= 5 ) {
		wp_send_json_error( [ 'message' => 'Muitas tentativas. Tente novamente em 1 hora.' ] );
	}
	set_transient( $rate_key, $attempts + 1, HOUR_IN_SECONDS );

	global $wpdb;
	$table    = $wpdb->prefix . 'newsletter_subscribers';
	$existing = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email ) );

	if ( $existing ) {
		wp_send_json_error( [ 'message' => 'Este email já está cadastrado.' ] );
	}

	$inserted = $wpdb->insert( $table, [
		'email'         => $email,
		'ip_address'    => $ip,
		'subscribed_at' => current_time( 'mysql' ),
		'status'        => 'active',
	], [ '%s', '%s', '%s', '%s' ] );

	if ( $inserted === false ) {
		wp_send_json_error( [ 'message' => 'Erro ao cadastrar. Tente novamente.' ] );
	}

	wp_send_json_success( [ 'message' => 'Cadastro realizado! Obrigado por assinar.' ] );
}

add_action( 'admin_menu', function () {
	add_menu_page(
		'Newsletter',
		'Newsletter',
		'manage_options',
		'newsletter-subscribers',
		'eletronicos_newsletter_admin_page',
		'dashicons-email-alt',
		30
	);
} );

function eletronicos_newsletter_admin_page() {
	global $wpdb;
	$table   = $wpdb->prefix . 'newsletter_subscribers';
	$nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
	$action  = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
	$id      = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
	$base    = admin_url( 'admin.php?page=newsletter-subscribers' );
	$notice  = '';

	if ( ! current_user_can( 'manage_options' ) ) return;

	// Export
	if ( $action === 'export' && wp_verify_nonce( $nonce, 'newsletter_export' ) ) {
		eletronicos_newsletter_export_csv();
		exit;
	}

	// Delete
	if ( $action === 'delete' && $id && wp_verify_nonce( $nonce, 'nl_delete_' . $id ) ) {
		$wpdb->delete( $table, [ 'id' => $id ], [ '%d' ] );
		wp_safe_redirect( add_query_arg( 'deleted', '1', $base ) );
		exit;
	}

	// Edit POST
	if ( $action === 'edit' && $id && isset( $_POST['nl_edit_nonce'] ) ) {
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nl_edit_nonce'] ) ), 'nl_edit_' . $id ) ) {
			wp_die( 'Nonce inválido.' );
		}
		$new_email  = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
		$new_status = in_array( $_POST['status'] ?? '', [ 'active', 'unsubscribed' ], true )
			? sanitize_text_field( wp_unslash( $_POST['status'] ) )
			: 'active';

		if ( ! is_email( $new_email ) ) {
			$notice = '<div class="notice notice-error"><p>Email inválido.</p></div>';
		} else {
			$wpdb->update( $table, [ 'email' => $new_email, 'status' => $new_status ], [ 'id' => $id ], [ '%s', '%s' ], [ '%d' ] );
			wp_safe_redirect( add_query_arg( 'updated', '1', $base ) );
			exit;
		}
	}

	// Notices from redirect
	if ( ! empty( $_GET['deleted'] ) ) $notice = '<div class="notice notice-success is-dismissible"><p>Assinante excluído.</p></div>';
	if ( ! empty( $_GET['updated'] ) ) $notice = '<div class="notice notice-success is-dismissible"><p>Assinante atualizado.</p></div>';

	// Edit form view
	if ( $action === 'edit' && $id ) {
		$sub = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
		if ( ! $sub ) wp_die( 'Assinante não encontrado.' );
		?>
		<div class="wrap">
			<h1><a href="<?php echo esc_url( $base ); ?>">← Newsletter</a> &rsaquo; Editar assinante</h1>
			<?php echo $notice; // phpcs:ignore ?>
			<form method="post" style="max-width:480px;margin-top:1.5rem">
				<?php wp_nonce_field( 'nl_edit_' . $id, 'nl_edit_nonce' ); ?>
				<table class="form-table">
					<tr>
						<th><label for="nl-email">Email</label></th>
						<td><input id="nl-email" name="email" type="email" class="regular-text" value="<?php echo esc_attr( $sub->email ); ?>" required></td>
					</tr>
					<tr>
						<th><label for="nl-status">Status</label></th>
						<td>
							<select id="nl-status" name="status">
								<option value="active" <?php selected( $sub->status, 'active' ); ?>>Ativo</option>
								<option value="unsubscribed" <?php selected( $sub->status, 'unsubscribed' ); ?>>Descadastrado</option>
							</select>
						</td>
					</tr>
				</table>
				<p class="submit">
					<button type="submit" class="button button-primary">Salvar</button>
					<a href="<?php echo esc_url( $base ); ?>" class="button">Cancelar</a>
				</p>
			</form>
		</div>
		<?php
		return;
	}

	// List view
	$subscribers = $wpdb->get_results( "SELECT * FROM {$table} ORDER BY subscribed_at DESC" );
	$total       = count( $subscribers );
	$export_url  = wp_nonce_url( add_query_arg( [ 'page' => 'newsletter-subscribers', 'action' => 'export' ], admin_url( 'admin.php' ) ), 'newsletter_export' );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">Newsletter</h1>
		<a href="<?php echo esc_url( $export_url ); ?>" class="page-title-action">Exportar para Excel</a>
		<hr class="wp-header-end">
		<?php echo $notice; // phpcs:ignore ?>
		<p>Total de assinantes: <strong><?php echo (int) $total; ?></strong></p>
		<table class="widefat striped" style="margin-top:1rem">
			<thead>
				<tr>
					<th>ID</th>
					<th>Email</th>
					<th>Data de cadastro</th>
					<th>Status</th>
					<th>Ações</th>
				</tr>
			</thead>
			<tbody>
				<?php if ( empty( $subscribers ) ) : ?>
					<tr><td colspan="5">Nenhum assinante cadastrado ainda.</td></tr>
				<?php else : foreach ( $subscribers as $sub ) :
					$edit_url   = add_query_arg( [ 'action' => 'edit', 'id' => $sub->id ], $base );
					$delete_url = wp_nonce_url( add_query_arg( [ 'action' => 'delete', 'id' => $sub->id ], $base ), 'nl_delete_' . $sub->id );
					$status_label = $sub->status === 'active' ? '<span style="color:#008a00">Ativo</span>' : '<span style="color:#a00">Descadastrado</span>';
				?>
					<tr>
						<td><?php echo (int) $sub->id; ?></td>
						<td><?php echo esc_html( $sub->email ); ?></td>
						<td><?php echo esc_html( $sub->subscribed_at ); ?></td>
						<td><?php echo $status_label; // phpcs:ignore ?></td>
						<td>
							<a href="<?php echo esc_url( $edit_url ); ?>">Editar</a>
							&nbsp;|&nbsp;
							<a href="<?php echo esc_url( $delete_url ); ?>" style="color:#a00"
							   onclick="return confirm('Excluir este assinante?')">Excluir</a>
						</td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table>
	</div>
	<?php
}

function eletronicos_newsletter_export_csv() {
	global $wpdb;
	$table       = $wpdb->prefix . 'newsletter_subscribers';
	$subscribers = $wpdb->get_results(
		"SELECT id, email, subscribed_at, ip_address, status FROM {$table} ORDER BY subscribed_at DESC",
		ARRAY_A
	);

	$filename = 'newsletter-assinantes-' . gmdate( 'Y-m-d' ) . '.csv';
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	$out = fopen( 'php://output', 'w' );
	fwrite( $out, "\xEF\xBB\xBF" ); // BOM UTF-8 para Excel
	fputcsv( $out, [ 'ID', 'Email', 'Data de cadastro', 'IP', 'Status' ], ';' );
	foreach ( $subscribers as $row ) {
		fputcsv( $out, $row, ';' );
	}
	fclose( $out );
}
