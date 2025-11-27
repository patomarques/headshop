<?php
/**
 * Template Correios Multsite settings.
 *
 * @package virtuaria/integrations/correios.
 */

defined( 'ABSPATH' ) || exit;

$states = array(
	'AC' => 'Acre',
	'AL' => 'Alagoas',
	'AP' => 'Amapa',
	'AM' => 'Amazonas',
	'BA' => 'Bahia',
	'CE' => 'Ceara',
	'DF' => 'Distrito Federal',
	'ES' => 'Espirito Santo',
	'GO' => 'Goias',
	'MA' => 'Maranhao',
	'MT' => 'Mato Grosso',
	'MS' => 'Mato Grosso do Sul',
	'MG' => 'Minas Gerais',
	'PA' => 'Pará',
	'PB' => 'Paraiba',
	'PR' => 'Paraná',
	'PE' => 'Pernambuco',
	'PI' => 'Piaui',
	'RJ' => 'Rio de Janeiro',
	'RN' => 'Rio Grande do Norte',
	'RS' => 'Rio Grande do Sul',
	'RO' => 'Rondônia',
	'RR' => 'Roraima',
	'SC' => 'Santa Catarina',
	'SP' => 'São Paulo',
	'SE' => 'Sergipe',
	'TO' => 'Tocantins',
);

$options = Virtuaria_WPMU_Correios_Settings::get_settings();

if ( ! isset( $options['username'] )
	&& ! class_exists( 'Extra_Checkout_Fields_For_Brazil' ) ) {
	$options['activate_checkout'] = 'yes';
}

$options_serialized = wp_json_encode( $options );

?>

<h1 class="main-title">Virtuaria Correios</h1>

<form action="" method="post" id="mainform" class="main-setting">
	<div class="navigation-tab">
		<a class="tablinks integration active" href="#">Integração</a>
		<a class="tablinks ticket" href="#">Etiquetas</a>
		<a class="tablinks checkout" href="#">Checkout</a>
		<a class="tablinks entrega" href="#">Instruções</a>
		<a class="tablinks premium" href="#">Premium</a>
		<a class="tablinks backup" href="#">Backup</a>
	</div>
	<table class="form-table integration">
		<tbody>
			<?php
			if ( is_multisite() && function_exists( 'is_main_site' ) && is_main_site() ) :
				?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_enabled">Habilitar Configuração Global</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Habilitar Configuração Global</span></legend>
							<input
								type="checkbox"
								name="woocommerce_virt_correios_enabled"
								id="woocommerce_virt_correios_enabled"
								value="yes"
								<?php isset( $options['enabled'] ) ? checked( $options['enabled'], 'yes' ) : ''; ?> />
							<p class="description">
								Habilita configuração global das informações de acesso a API e geração de etiquetas.
							</p>
						</fieldset>
					</td>
				</tr>
				<?php
			endif;
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_easy_mode">Modo sem Contrato</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Modo sem Contrato</span></legend>
						<input type="checkbox" style="display: inline;"
							name="woocommerce_virt_correios_easy_mode"
							id="woocommerce_virt_correios_easy_mode"
							value="yes"
							<?php isset( $options['easy_mode'] ) ? checked( $options['easy_mode'], 'yes' ) : ''; ?> />
						<p class="description" style="display: inline;">
							Permite realizar cotações de frete <b>sem a necessidade de um contrato com os Correios</b>. Vale ressaltar que apenas os métodos SEDEX ( 03220 ) e PAC (03298) estarão disponíveis. Os valores serão calculadas com base na modalidade de pagamento à vista, devendo ser confirmadas no ato da postagem.
							<span class="warning">
								Atenção: Não é possível gerar Etiquetas no Modo sem Contrato.
							</span>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr class="separator"></tr>
			<tr valign="top">
				<th scope="row" class="titledesc section">
					Contrato
				</th>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_username">Usuário</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Usuário</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_username"
							id="woocommerce_virt_correios_username"
							value="<?php echo isset( $options['username'] ) ? esc_attr( $options['username'] ) : ''; ?>" />
						<p class="description">
							Usuário cadastrado no painel do site <a href="https://meucorreios.correios.com.br/app/index.php">"Meus Correios"</a>. Normalmente é o CNPJ, apenas com números, sem pontuação. Veja como encontrar no portal <a href="https://cas.correios.com.br/login" target="_blank">CAS</a>.
							<a href="#" onclick="openModal('loginVideoModal'); return false;">Veja como encontrar no portal CAS<i class="videoicon dashicons dashicons-video-alt3"></i></a>.
							<a href="#" onclick="openModal('loginVideoModal2'); return false;">Veja como encontrar no portal Empresas<i class="videoicon dashicons dashicons-video-alt3"></i></a>.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_password">Código de acesso</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Código de acesso</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_password"
							id="woocommerce_virt_correios_password"
							value="<?php echo isset( $options['password'] ) ? esc_attr( $options['password'] ) : ''; ?>" />
						<p class="description">
							Código de acesso gerado no portal do serviço Meus Correios.
							<a href="#" onclick="openModal('accessCodeVideoModal'); return false;">Veja como encontrar<i class="videoicon dashicons dashicons-video-alt3"></i></a>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_post_card">Cartão de Postagem</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Cartão de Postagem</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_post_card"
							id="woocommerce_virt_correios_post_card"
							value="<?php echo isset( $options['post_card'] ) ? esc_attr( $options['post_card'] ) : ''; ?>" />
						<p class="description">
							Recurso utilizado para acesso a APIs. Normalmente tem 10 dígitos
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="enviroment">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_enviroment">Ambiente</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Ambiente</span></legend>
						<select
							name="woocommerce_virt_correios_enviroment"
							id="woocommerce_virt_correios_enviroment">
							<option
							<?php
							if ( isset( $options['enviroment'] ) ) {
								echo selected( 'sandbox', $options['enviroment'], false );
							}
							?>
							value="sandbox">Testes</option>
							<option
							<?php
							if ( isset( $options['enviroment'] ) ) {
								echo selected( 'production', $options['enviroment'], false );
							}
							?>
							value="production">Produção</option>
						</select>
						<p class="description">
							Modo de execução da integração com correios.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr class="separator"></tr>
			<tr valign="top" class="general-resources">
				<th scope="row" class="titledesc section">
					Recursos Gerais
					<small style="display: block;font-weight: normal;font-size: 15px;max-width:250px;white-space:nowrap;">
						Configura recursos que afetam toda a loja.
					</small>
				</th>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_automatic_fill">Preenchimento automático</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Preenchimento automático</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_automatic_fill"
							id="woocommerce_virt_correios_automatic_fill"
							value="yes"
							<?php isset( $options['automatic_fill'] ) ? checked( $options['automatic_fill'], 'yes' ) : ''; ?> />
						<p class="description">
							Habilita o preenchimento automático de endereços com base no CEP.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_calc_in_product">Calculadora na página do produto</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Calculadora na página do produto</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_calc_in_product"
							id="woocommerce_virt_correios_calc_in_product"
							value="yes"
							<?php isset( $options['calc_in_product'] ) ? checked( $options['calc_in_product'], 'yes' ) : ''; ?> />
						<p class="description">
							Exibir
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_calc_in_product">Shortcode para Calculadora</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Shortcode para Calculadora</span></legend>
						<p class="description">
							Para evitar exibição duplicada, desative a opção <b>"Calculadora na página do produto"</b>. Para exibir a calculadora de frete com mais flexibilidade na página do produto, use o
							<fieldset class="shortcode">
								<legend>Shortcode</legend>
								<b>[virtuaria_correios_calculadora]</b>
								<button class="copy-shortcode"><span class="dashicons dashicons-clipboard"></span></button>
							</fieldset>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_parcel_tracking">Rastreamento de encomendas</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Rastreamento de encomendas</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_parcel_tracking"
							id="woocommerce_virt_correios_parcel_tracking"
							value="yes"
							<?php isset( $options['parcel_tracking'] ) ? checked( $options['parcel_tracking'], 'yes' ) : ''; ?> />
						<p class="description">
							Exibe o rastreamento das entregas nos painéis do cliente e de edição do pedido para o lojista.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="<?php echo isset( $options['devolutions'] ) ? 'devolutions-ative' : ''; ?>">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_devolutions">Solicitação de devolução</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Solicitação de devolução</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_devolutions"
							id="woocommerce_virt_correios_devolutions"
							value="yes"
							<?php isset( $options['devolutions'] ) ? checked( $options['devolutions'], 'yes' ) : ''; ?> />
						<p class="description">
							Ative para que seus clientes possam solicitar devoluções de produtos diretamente no painel de pedidos do cliente. Sempre que uma devolução é solicitada, uma notificação por e-mail será enviada ao gestor da loja.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top" class="hide-devolution-if-off">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_hide_devolution_button">Ocultar botão de devolução após X Dias</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Ocultar botão de devolução após X Dias</span></legend>
						<input
							type="number"
							step="1"
							min="0"
							name="woocommerce_virt_correios_hide_devolution_button"
							id="woocommerce_virt_correios_hide_devolution_button"
							value="<?php echo isset( $options['hide_devolution_button'] ) ? esc_attr( $options['hide_devolution_button'] ) : ''; ?>" />
						<p class="description">
							Informe o número de dias a partir do qual não será mais possível solicitar devolução via painel do cliente.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_display_cart_fields">Exibir campos do carrinho</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Exibir campos extras no carrinho</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_display_cart_fields"
							id="woocommerce_virt_correios_display_cart_fields"
							value="yes"
							<?php isset( $options['display_cart_fields'] ) ? checked( $options['display_cart_fields'], 'yes' ) : ''; ?> />
						<p class="description">
							Marque para exibir os campos <b>País, Estado e Cidade</b> na calculadora de frete da página do carrinho.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_hide_free_shipping_notice">Esconder Mensagem de Frete Grátis</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Esconder Mensagem de Frete Grátis</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_hide_free_shipping_notice"
							id="woocommerce_virt_correios_hide_free_shipping_notice"
							value="yes"
							<?php isset( $options['hide_free_shipping_notice'] ) ? checked( $options['hide_free_shipping_notice'], 'yes' ) : ''; ?> />
						<p class="description">
							Marque para ocultar a mensagem exibida ao cliente quando a condição para frete grátis for atingida.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_disable_feedback">Desativar Alertas aos Clientes</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Desativar Alertas aos Clientes</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_disable_feedback"
							id="woocommerce_virt_correios_disable_feedback"
							value="yes"
							<?php isset( $options['disable_feedback'] ) ? checked( $options['disable_feedback'], 'yes' ) : ''; ?>/>
						<p class="description">
						Desativa a exibição de avisos aos cliente quando ocorrem problemas no cálculo de frete, como falhas na comunicação com a API dos Correios ou situações em que o pedido não atende aos requisitos de envio (ex.: Dimensões que excedem os limites dos correios para um determinado método de envio).
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_compatibility_trakking_code">Exibir Codigo de Rastreio de Outros Plugins</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Exibir Codigo de Rastreio de Outros Plugins</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_compatibility_trakking_code"
							id="woocommerce_virt_correios_compatibility_trakking_code"
							value="yes"
							<?php isset( $options['compatibility_trakking_code'] ) ? checked( $options['compatibility_trakking_code'], 'yes' ) : ''; ?>/>
						<p class="description">
							Ative esta configuração para permitir que o plugin Virtuaria Correios reconheça e rastreie códigos de rastreamento gerados por outros plugins utilizados anteriormente na sua loja.
						</p>
					</fieldset>
				</td>
			</tr>
			<!-- <tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_optimize_add_cart">Desativar aceleração no Adicionar ao Carrinho</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Desativar aceleração no Adicionar ao Carrinho</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_optimize_add_cart"
							id="woocommerce_virt_correios_optimize_add_cart"
							value="yes"
							/>
						<p class="description">
							Marque para desativar a otimização que acelera o processo de adição de produtos ao carrinho.
						</p>
					</fieldset>
				</td>
			</tr> -->
			<tr class="separator"></tr>
			<tr valign="top">
				<th scope="row" class="titledesc section">
					Depuração
				</th>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_debug">Debug</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Debug</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_debug"
							id="woocommerce_virt_correios_debug"
							value="yes"
							<?php isset( $options['debug'] ) ? checked( $options['debug'], 'yes' ) : ''; ?> />
						<p class="description">
							Log para depuração de problemas.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_error_message">Mensagens dos Correios</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Mensagens dos Correios</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_error_message"
							id="woocommerce_virt_correios_error_message"
							value="yes"
							<?php isset( $options['error_message'] ) ? checked( $options['error_message'], 'yes' ) : ''; ?> />
						<p class="description">
							Exibe descritivo de problemas na cotação de frete nas áreas abertas de sua loja.
						</p>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
	<table class="form-table ticket hidden">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc section">
					<small style="display: block;font-size: 15px;color: #4c4f57;font-weight: bold;margin-top: 10px;width: 1058px;" class="ticket-desc">
						📦 Define os dados usados na emissão da pré-postagem de encomendas. Atenção: <span style="color: #19a236;">Geração de Etiqueta</span> e 
						<span style="color: #19a236;">Declaração</span> são recursos da versão <span style="font-weight: bold; color: #007bff;"> Free.</span>
					</small>
				</th>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc section">
					Impressão
				</th>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					Formato de Impressão
					<small style="display: block;font-weight: normal;font-size: 15px;">
						Define o formato a ser utilizado na impressão de etiquetas.
					</small>
				</th>
				<td class="forminp">
					<fieldset>
						<select name="woocommerce_virt_correios_print_format" id="" onchange="alert('Esta configuração será aplicada somente às próximas etiquetas geradas.');">
							<option <?php isset( $options['print_format'] ) ? selected( $options['print_format'], 'PADRAO' ) : ''; ?> value="PADRAO">10,5 x 12 cm ( com borda, indicada para A4 )</option>
							<option <?php isset( $options['print_format'] ) ? selected( $options['print_format'], 'LINEAR_100_150' ) : ''; ?> value="LINEAR_100_150">10 x 15 cm ( indicada para impressora térmica de etiquetas )</option>
							<option <?php isset( $options['print_format'] ) ? selected( $options['print_format'], 'LINEAR_100_80' ) : ''; ?> value="LINEAR_100_80">10 x 8 cm</option>
							<option <?php isset( $options['print_format'] ) ? selected( $options['print_format'], 'LINEAR_A4' ) : ''; ?> value="LINEAR_A4">10,5 x 12 cm ( sem borda, indicada para A4 )</option>
						</select>
					</fieldset>
				</td>
			</tr>
			<tr class="separator"></tr>
			<tr valign="top">
				<th scope="row" class="titledesc section">
					Remetente
				</th>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_full_name">Nome Completo</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Nome Completo</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_full_name"
							id="woocommerce_virt_correios_full_name"
							maxlength="50"
							value="<?php echo isset( $options['full_name'] ) ? esc_attr( $options['full_name'] ) : ''; ?>" />
						<p class="description">
							Nome completo do remetente dos pacotes. Pessoa Física ou Jurídica.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_ddd">Código de área (DDD)</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Código de área (DDD)</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_ddd"
							id="woocommerce_virt_correios_ddd"
							maxlength="2"
							value="<?php echo isset( $options['ddd'] ) ? esc_attr( $options['ddd'] ) : ''; ?>" />
						<p class="description">
							Código de área (DDD) do remetente dos pacotes.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_fone">Celular</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Celular</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_fone"
							id="woocommerce_virt_correios_fone"
							maxlength="9"
							value="<?php echo isset( $options['fone'] ) ? esc_attr( $options['fone'] ) : ''; ?>" />
						<p class="description">
							Número do celular do remetente. Sem o Código de área (DDD).
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_email">E-mail</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>E-mail</span></legend>
						<input
							type="email"
							name="woocommerce_virt_correios_email"
							id="woocommerce_virt_correios_email"
							value="<?php echo isset( $options['email'] ) ? esc_attr( $options['email'] ) : ''; ?>" />
						<p class="description">
							E-mail do remetente.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_cpfcnpj">CPF / CNPJ</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>CPF / CNPJ</span></legend>
						<input
							type="number"
							step="1"
							name="woocommerce_virt_correios_cpfcnpj"
							id="woocommerce_virt_correios_cpfcnpj"
							maxlength="14"
							value="<?php echo isset( $options['cpfcnpj'] ) ? esc_attr( $options['cpfcnpj'] ) : ''; ?>" />
						<p class="description">
							Documento de identificação do remetente. Informe o CPF ou CNPJ, somente números são aceitos.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_origin">CEP</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>CEP</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_origin"
							id="woocommerce_virt_correios_origin"
							maxlength="8"
							value="<?php echo isset( $options['origin'] ) ? esc_attr( $options['origin'] ) : ''; ?>" />
						<p class="description">
							Código postal (CEP) do remetente dos pacotes ( somente números ).
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_logradouro">Logradouro</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Logradouro</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_logradouro"
							id="woocommerce_virt_correios_logradouro"
							maxlength="50"
							value="<?php echo isset( $options['logradouro'] ) ? esc_attr( $options['logradouro'] ) : ''; ?>" />
						<p class="description">
							Logradouro do remetente. Máximo de 50 caracteres.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_numero">Número</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Número</span></legend>
						<input
							type="number"
							step="1"
							name="woocommerce_virt_correios_numero"
							id="woocommerce_virt_correios_numero"
							maxlength="6"
							value="<?php echo isset( $options['numero'] ) ? esc_attr( $options['numero'] ) : ''; ?>" />
						<p class="description">
							Número do logradouro do remetente. Máximo de 6 caracteres.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_complemento">Complemento ( Opcional )</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Complemento ( Opcional )</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_complemento"
							id="woocommerce_virt_correios_complemento"
							maxlength="30"
							value="<?php echo isset( $options['complemento'] ) ? esc_attr( $options['complemento'] ) : ''; ?>" />
						<p class="description">
							Complemento do logradouro do remetente. Máximo de 30 caracteres.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_bairro">Bairro</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Bairro</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_bairro"
							id="woocommerce_virt_correios_bairro"
							maxlength="30"
							value="<?php echo isset( $options['bairro'] ) ? esc_attr( $options['bairro'] ) : ''; ?>" />
						<p class="description">
							Bairro do remetente. Máximo de 30 caracteres.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_cidade">Cidade</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Cidade</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_cidade"
							id="woocommerce_virt_correios_cidade"
							maxlength="30"
							value="<?php echo isset( $options['cidade'] ) ? esc_attr( $options['cidade'] ) : ''; ?>" />
						<p class="description">
						Cidade do remetente. Máximo de 30 caracteres.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_estado">Estado</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Estado</span></legend>
						<select
							name="woocommerce_virt_correios_estado"
							id="woocommerce_virt_correios_estado">
							<option value="">--Selecione--</option>
							<?php
							foreach ( $states as $key => $value ) {
								printf(
									'<option value="%s" %s>%s</option>',
									esc_attr( $key ),
									$options['estado'] === $key ? 'selected' : '',
									esc_html( $value )
								);
							}
							?>
						</select>
						<p class="description">
							Estado (UF) do remetente.
						</p>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
	<table class="form-table entrega hidden" style="margin-top:20px; margin-bottom:50px; margin-left: 20px;">
		<tbody>
			<tr valign="top">
				<td><h2><i class="dashicons dashicons-archive"></i> Configurar Entrega</h2></td>
			</tr>
			<tr valign="top">
				<td><h4>✅ 1. Selecione uma "Área de Entrega";</h4></td>
			</tr>
			<tr valign="top">
				<td><h4>✅ 2. Clique em "Adicionar método de entrega";</h4></td>
			</tr>
		
			<tr valign="top">
				<td><h4>✅ 3. Selecione "Virtuaria Correios" e clique em "Adicionar";</h4></td>
			</tr>
			<tr valign="top">
				<td><h4>✅ 4. Após "Adicionar", você poderá definir um título e  escolher um dos produtos dos correios (PAC, SEDEX, etc)</h4></td>
			</tr>
			<tr valign="top">
				<td>
					<div class="actions">
						<h4>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping' ) ); ?>">
								Clique para Adicionar um Método de Entrega
							</a>
						</h4>
						<button id="fast-start-button" class="button-primary">Criar Métodos Automaticamente (PAC / SEDEX)</button>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<hr class="separator" />
					<h2><i class="videoicon dashicons dashicons-video-alt3"></i> Configure Métodos de Entrega no WooCommerce e Otimize sua Logística!</h2>
					<iframe width="600" height="400" src="https://www.youtube.com/embed/NTn0nofYVKo?si=NHGyhvNVkuGbwU_R" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
					<h2><i class="videoicon dashicons dashicons-video-alt3"></i> Visão Geral</h2>
					<iframe width="600" height="400" src="https://www.youtube.com/embed/oy0H-KOh3Gc?si=P3mxRxPK6GGxrxfR" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
				</td>
			</tr>
			<tr>
				<td>
					<hr class="separator" />
					<h2><i class="dashicons dashicons-menu-alt3"></i> Dúvidas Frequentes</h2>
					<ol class="frequently-questions">
						<li>
							✅ Etiquetas e Declaração de Conteúdo
							<ul class="ticket faq">
								<li>É obrigatório configurar um contrato para gerar;</li>
								<li>Está incluso na versão gratuita do plugin, porém na versão Premium existe a facilidade de gerar mais rapidamente via tela Entregas</li>
								<li>É possível regerar Etiquetas ou Declaração para um mesmo pedido, porém apenas via tela de Detalhes do Pedido;</li>
							</ul>
						</li>
						<li>
							✅ Calculadora na Página do Produto
							<ul class="calc-product faq">
								<li>Funciona com produtos simples ou variáveis;</li>
								<li>O cálculo é realizado com base no produto em exibição, não nos produtos que estão no carrinho;</li>
								<li>Opcionalmente, é possível usar o shortcode virtuaria_correios_calculadora para incluir a calculadora de forma mais flexível.</li>
							</ul>
						</li>
						<li>
							✅ Modo sem Contrato
							<ul class="basic-mode faq">
								<li>Faz a contação do frete e prazo de entrega sem necessidade de contrato com os correios;</li>
								<li>Quando ativo, não permite gerar etiquetas ou declarações de conteúdo.</li>
							</ul>
						</li>
						<li>
							✅ Etiqueta de envio internacional
							<ul class="international-prepost faq">
								<li>Atualmente, os Correios não disponibilizam Pré-Postagem (etiquetas) para envio internacional.</li>
							</ul>
						</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td>
					<div class="container">
						<div class="text-content">
							<h1>Orientação sobre Exportação</h1>
							<p>Precisa de ajuda para exportar seus produtos?</p>
							<a href="https://forms.office.com/pages/responsepage.aspx?id=9Z0GAC-sp0y5pdmf6jerAivNEGPT0jxHiY5UqhUhyENURU81SlhER0ZLQVpFQzhWS1ZPNzlQRk1VVS4u" target="_blank" class="cta-button">Solicitar Atendimento</a>
							<p>Um consultor dos Correios entrará em contato para auxiliá-lo com todas as informações e processos necessários para exportação.</p>
						</div>
						<div class="image-container">
							<img src="<?php echo esc_url( VIRTUARIA_CORREIOS_URL ); ?>admin/images/correios-exportacao.webp" alt="Exportação Correios">
						</div>
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<table class="form-table checkout hidden">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc section">
					<label for="woocommerce_virt_correios_activate_checkout">Marque para Ativar</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Marque para Ativar</span></legend>
						<input
							type="checkbox"
							name="woocommerce_virt_correios_activate_checkout"
							id="woocommerce_virt_correios_activate_checkout"
							value="yes"
							<?php isset( $options['activate_checkout'] ) ? checked( $options['activate_checkout'], 'yes' ) : ''; ?> />
						<p class="description">
							<span class="label-setting" style="font-size: 20px;color: #272727;font-weight: bold;">
								Campos de Checkout do Brasil
							</span>
							<h3>Destaques</h3>
							<ul class="features">
								<li>Substitui o plugin Brazilian Market on Woocommerce</li>
								<li>Preenche campos do checkout somente com a edição do CEP</li>
								<li>Preenchimento automático dos campos do checkout, funciona no checkout modo classico e blocos</li>
								<li>Permite incluir na loja virtual campos como CPF, CNPJ, RG, entre outros. São campos frequentemente necessários para integração com plataformas diversas do Brasil, como por exemplo, plataformas de logística ou pagamentos.</li>
							</ul>
						</p>
					</fieldset>
				</td>
			</tr>
			<?php
			if ( isset( $options['activate_checkout'] ) ) {
				do_action( 'virtuaria_correios_setting_extra_fields' );
			}
			?>
		</tbody>
	</table>
	<table class="form-table premium hidden">
		<tbody>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<small style="display: block;font-size: 15px;color: #4c4f57;width: 1058px;" class="ticket-desc">
						🆓 🔓 Este plugin é gratuito e de código aberto. Os recursos Premium são adicionais que proporcionam mais agilidade a operações já disponíveis gratuitamente, como a geração de etiquetas.
					</small>
				</th>
			</tr>
			<tr valign="top" class="serial-code">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_serial">Código de Licença</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Código de Licença</span></legend>
						<input
							type="text"
							name="woocommerce_virt_correios_serial"
							id="woocommerce_virt_correios_serial"
							value="<?php echo isset( $options['serial'] ) ? esc_attr( $options['serial'] ) : ''; ?>" />
						<p class="description">
							Informe o código de licença para ter acesso a todos os recursos <b>premium</b> do plugin.
						</p>
						<?php
						if ( ! isset( $options['serial'], $options['authenticated'] )
							|| ! $options['serial']
							|| ! $options['authenticated'] ) :
							?>
							<p class="description">
								<b>Status: <span style="color:red">Desativado</span></b><br>
								Você ainda não possui um Código de Licença válido. É possível adquirir através do link <a href="https://virtuaria.com.br/loja/virtuaria-correios/" target="_blank">https://virtuaria.com.br/loja/virtuaria-correios</a>. Em caso de dúvidas, entre em contato com o suporte via e-mail <a href="mailto:integracaocorreios@virtuaria.com.br">integracaocorreios@virtuaria.com.br</a>.
							</p>
							<?php
						else :
							?>
							<p class="description">
								<b>Status: <span style="color:green">Ativado</span></b><br>
								Você possui uma chave de acesso válida. Em caso de dúvidas, entre em contato com o suporte via e-mail <a href="mailto:integracaocorreios@virtuaria.com.br">integracaocorreios@virtuaria.com.br</a>.
							</p>
							<?php
						endif;
						?>
						</p>
					</fieldset>
				</td>
			</tr>
			<tr class="separator"></tr>
			<?php
			if ( ! isset( $options['serial'], $options['authenticated'] )
				|| ! $options['serial']
				|| ! $options['authenticated'] ) :
				?>
				<tr valign="top">
					<th scope="row" class="titledesc" style="width: 0;">
						
					</th>
					<td>
						<div class="premium-disabled form-table hidden premium">
							<h2>Recursos Premium</h2>
							<p class="description">
								Com nossa versão premium, você terá acesso a funcionalidades avançadas que vão melhorar a gestão dos envios. Um plugin confiável e poderoso, capaz de transformar a gestão logística do seu e-commerce. Invista no nosso plugin premium e maximize o potencial do seu e-commerce! Confira abaixo a lista de recursos disponíveis: 
							</p>
							<ul>
								<!-- <li><h3>💡 Preço por Categoria</h3>Ajusta os custos do frete com base nas categorias dos produtos, aumentando, diminuindo ou fixando preços, conforme a necessidade. Essa funcionalidade permite uma abordagem flexível para gerenciar os custos de envio de acordo com o perfil dos produtos.</li> -->
								<li><h3>💡 Geração automática de Etiquetas.</h3>Cria automaticamente uma etiqueta de entrega com declaração de itens, sem nota fiscal, no momento em que o pedido é pago (processando, concluído).</li>
								<li><h3>📬 Envio de e-mail com Código de Rastreio.</h3>Permte controle sobre o envio automático de notificação de e-mail com o código de rastreamento para o cliente quando uma nova etiqueta for criada.</li>
								<li><h3>📊 Barra de Progresso para Frete Grátis</h3>Incentiva compras de maior valor ao mostrar o quanto falta para alcançar o frete grátis. Proporciona uma visualização clara e motivadora do progresso em direção ao frete grátis, com uma barra de progresso visível no checkout e carrinho.</li>
								<li><h3>🎯 Shortcode [progress_free_shipping]</h3>Flexibilidade é a chave, e com este shortcode, você pode exibir a barra de progresso para frete grátis em qualquer lugar do seu site. Seja na página inicial, em páginas de produtos específicos ou até mesmo em campanhas promocionais, essa ferramenta permite uma integração fluida e adaptável ao layout do seu site.</li>
								<li><h3>🚚 Esconder Métodos de Entrega</h3>Simplifique o processo de escolha do cliente ao oferecer frete grátis. Quando o método de envio gratuito está disponível, essa função oculta automaticamente todos os outros métodos de entrega, garantindo uma experiência de compra mais direta e intuitiva.</li>
								<li><h3>✨ Frete Grátis</h3>O frete grátis do plugin permite que os métodos de envio dos Correios tenham um custo zero quando o valor mínimo para obtenção do frete grátis, configurado pelo usuário, é alcançado.</li>
								<li><h3>🏷️ Gerenciamento de Etiquetas no Relatório de Entregas</h3> Otimize a gestão logística de sua loja virtual com o gerenciamento de etiquetas no relatório de entregas. Assim é possível gerar e imprimir etiquetas de envio diretamente do relatório de entrega de forma ágil e eficiente.</li>
								<li>
									<h2>🌟  Seja Premium</h2>
									Entre em contato conosco e garanta uma experiência otimizada para sua loja virtual.<br>
									E-mail: <a href="mailto:sejapremium@virtuaria.com.br">sejapremium@virtuaria.com.br</a><br>
									WhatsApp: +55 79 999312134
								</li>
								<!-- <li class="gallery">
									<ul class="premium-prints">
										<li class="print">
											<a href="<?php //echo esc_attr( VIRTUARIA_CORREIOS_URL ); ?>admin/images/print-01.jpg" target="_blank">
												<img src="<?php //echo esc_attr( VIRTUARIA_CORREIOS_URL ); ?>admin/images/print-01.jpg" alt="Print" />
												<h3 class="description">Gestão de Etiquetas no Relatório de Entregas</h3>
											</a>
										</li>
										<li class="print">
											<a href="<?php //echo esc_attr( VIRTUARIA_CORREIOS_URL ); ?>admin/images/print-02.jpg" target="_blank">
												<img src="<?php //echo esc_attr( VIRTUARIA_CORREIOS_URL ); ?>admin/images/print-02.jpg" alt="Print" />
												<h3 class="description">Ajuste de Preço por Categoria</h3>
											</a>
										</li>
										<li class="print">
											<a href="<?php //echo esc_attr( VIRTUARIA_CORREIOS_URL ); ?>admin/images/print-03.jpg" target="_blank">
												<img src="<?php // esc_attr( VIRTUARIA_CORREIOS_URL ); ?>admin/images/print-03.jpg" alt="Print" />
												<h3 class="description">Barra de Progresso para Frete GRATIS</h3>
											</a>
										</li>
									</ul>
								</li> -->
							</ul>
						</div>
					</td>
				<?php
			else :
				?>
				<tr valign="top">
					<th scope="row" class="titledesc section">
						Recursos Premium
					</th>
				</tr>
				<!-- <tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_category_price">Preço por categoria</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Preço por categoria</span></legend>
							<input
								type="checkbox"
								name="woocommerce_virt_correios_category_price"
								id="woocommerce_virt_correios_category_price"
								value="yes"
								isset( $options['category_price'] ) ? checked( 'yes', $options['category_price'] ) : ''; ?> />
							<p class="description">
								Permite aumentar, diminuir ou fixar o preço de frete para produtos das categorias selecionadas no método de entrega.
							</p>
						</fieldset>
					</td>
				</tr> -->
				<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_automatic_prepost">Pré-Postagem Automática</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Pré-Postagem Automática</span></legend>
							<input
								type="checkbox"
								name="woocommerce_virt_correios_automatic_prepost"
								id="woocommerce_virt_correios_automatic_prepost"
								value="yes"
								<?php isset( $options['automatic_prepost'] ) ? checked( 'yes', $options['automatic_prepost'] ) : ''; ?> />
							<p class="description">
								Gera automaticamente etiqueta de pré-postagem quando o pedido for pago pelo cliente. A impressão da etiqueta poderá ser feita no relatório de Entregas ou na edição do pedido.
							</p>
						</fieldset>
					</td>
				</tr>
				<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_automatic_prepost">Desativar Envio de e-mail com Código de Rastreio</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Desativar Envio de e-mail com Código de Rastreio</span></legend>
							<input
								type="checkbox"
								name="woocommerce_virt_correios_disable_email_tracking_code"
								id="woocommerce_virt_correios_disable_email_tracking_code"
								value="yes"
								<?php isset( $options['disable_email_tracking_code'] ) ? checked( 'yes', $options['disable_email_tracking_code'] ) : ''; ?> />
							<p class="description">
								Desativa o envio automático de notificação de e-mail com o código de rastreamento para o cliente quando uma nova etiqueta é criada.
							</p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_progress_free">Barra de progresso para frete grátis</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Barra de progresso para frete grátis</span></legend>
							<input
								type="checkbox"
								name="woocommerce_virt_correios_progress_free"
								id="woocommerce_virt_correios_progress_free"
								value="yes"
								<?php isset( $options['progress_free'] ) ? checked( 'yes', $options['progress_free'] ) : ''; ?> />
							<p class="description">
								Ao definir <b>"Valor Mínimo para Desconto no Frete"</b> com percentual <b>100% (Grátis)</b>, exibe na página de checkout e carrinho, valor que o cliente precisa adicionar para obter frete grátis. <b>Atenção: </b> A barra de frete grátis não é exibida quando a estimativa de frete for alterada por "Ajustar o valor do frete com base nas categorias de produtos".
							</p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_hide_shipping">Esconder Métodos de entrega</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Esconder Métodos de entrega</span></legend>
							<input
								type="checkbox"
								name="woocommerce_virt_correios_hide_shipping"
								id="woocommerce_virt_correios_hide_shipping"
								value="yes"
								<?php isset( $options['hide_shipping'] ) ? checked( 'yes', $options['hide_shipping'] ) : ''; ?> />
							<p class="description">
								Quando o método frete grátis estiver disponível, esconde todos os demais métodos de entrega.
							</p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_shorcode">Shortcode</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Shortcode</span></legend>
							<p class="description">
								Flexibilidade é a chave, e com este shortcode <b>[progress_free_shipping]</b>, você pode exibir a barra de progresso para frete grátis em qualquer lugar do seu site. Seja na página inicial, em páginas de produtos específicos ou até mesmo em campanhas promocionais, essa ferramenta permite uma integração fluida e adaptável ao layout do seu site.
							</p>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_shipping_screen">Gerenciar Etiquetas no Relatório de Entregas</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Gerenciar Etiquetas no Relatório de Entregas</span></legend>
							<p class="description">
								Otimize a gestão logística de sua loja virtual com o gerenciamento de etiquetas no relatório de entregas. Assim é possível gerar e imprimir etiquetas de envio diretamente do relatório de entrega de forma ágil e eficiente.
							</p>
						</fieldset>
					</td>
				</tr>
				<?php
			endif;
			?>
		</tbody>
	</table>
	<table class="form-table backup hidden">
		<tbody>
			<?php
			if ( get_option( 'woocommerce_correios-integration_settings' ) ) :
				?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="woocommerce_virt_correios_import_woocommerce_correios">Plugin Woocommerce Correios do Cláudio Sanches</label>
					</th>
					<td class="forminp">
						<fieldset>
							<legend class="screen-reader-text"><span>Plugin Woocommerce Correios do Cláudio Sanches</span></legend>
							<button
								id="import-contract-woocommerce-correios"
								class="button button-primary import-preferences">
								IMPORTAR CONFIGURAÇÕES
							</button>

							<p class="description">
								Inicie rapidamente. Importe os dados do contrato utilizados no plugin Woocommerce Correios do Cláudio Sanches.
							</p>
							<input type="hidden" name="should_import_woocommerce_correios_preferences" value="no">
						</fieldset>
					</td>
				</tr>
				<?php
			endif;
			?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_preferences_import">Virtuaria Correios</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Virtuaria Correios</span></legend>
						<textarea
						name="woocommerce_virt_correios_preferences_import"
						id=""
						style="width: 100%;min-height: 100px; max-width: 640px;"></textarea>
						<button
						class="button button-primary import-preferences"
						id="import-preferences">IMPORTAR CONFIGURAÇÕES</button>
						<p class="description">
							Copie as informações da caixa "Configurações Atuais" de outra loja virtual e cole aqui para importar as configurações nesta loja virtual. Atenção: este processo irá sobrescrever as configurações atuais desta loja virtual. Recomendamos que faça um backup das configurações atuais, antes de executar.
						</p>
					</fieldset>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="woocommerce_virt_correios_preferences_export">Configurações Atuais</label>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span>Configurações Atuais</span></legend>
						<textarea
						name="woocommerce_virt_correios_preferences_export"
						id="woocommerce_virt_correios_preferences_export"
						readonly
						style="width: 100%;min-height: 100px; max-width: 640px;cursor:pointer"><?php echo esc_html( $options_serialized ); ?></textarea>
						<p class="description">
							Copie o conteúdo da caixa acima e salve em um local seguro, para fazer backup das configurações do plugin Virtuaria Correios.
						</p>
					</fieldset>
				</td>
			</tr>
		</tbody>
	</table>
	<div id="loginVideoModal" class="modal" style="display: none;">
		<div class="modal-content">
			<span class="close" onclick="closeModal('loginVideoModal')">×</span>
			<video width="560" height="315" controls="">
				<source src="https://virtuaria-plugins.s3.sa-east-1.amazonaws.com/correios/VirtuariaLoginCorreios.mp4" type="video/mp4">
				Seu navegador não suporta o elemento de vídeo.
			</video>
		</div>
	</div>

	<div id="loginVideoModal2" class="modal" style="display: none;">
		<div class="modal-content">
			<span class="close" onclick="closeModal('loginVideoModal2')">×</span>
			<video width="560" height="315" controls="">
				<source src="https://virtuaria-plugins.s3.sa-east-1.amazonaws.com/correios/VirtuariaLoginCorreiosEmpresas.mp4" type="video/mp4">
				Seu navegador não suporta o elemento de vídeo.
			</video>
		</div>
	</div>

	<div id="accessCodeVideoModal" class="modal" style="display: none;">
		<div class="modal-content">
			<span class="close" onclick="closeModal('accessCodeVideoModal')">×</span>
			<video width="560" height="315" controls="">
				<source src="https://virtuaria-plugins.s3.sa-east-1.amazonaws.com/correios/VirtuariaGerarTokenCorreios.mp4" type="video/mp4">
				Seu navegador não suporta o elemento de vídeo.
			</video>
		</div>
	</div>

	<div id="classicModeModal" class="modal" style="display: none;">
		<div class="modal-content">
			<span class="close" onclick="closeModal('classicModeModal')">×</span>
			<iframe width="560" height="315" src="https://www.youtube.com/embed/0Z18Htrg_Fs?si=1auXHALlHZ6xwes-" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
		</div>
	</div>

	<?php wp_nonce_field( 'update-correios-settings', 'correios_nonce' ); ?>
	<input
		type="submit"
		class="button button-primary"
		value="<?php esc_attr_e( 'Salvar alterações', 'virtuaria-correios' ); ?>">
</form>
<p class="description" style="margin-top: 20px; font-size: 15px; margin-bottom: -5px;">
	Alguns serviços possuem limitações relacionadas a preço, peso e dimensão e podem não estar disponíveis para todos os seus produtos. Para mais informações, consulte a documentação dos <a href="https://www.correios.com.br/enviar/servicos-adicionais" target="_blank">Correios</a>.
</p>
