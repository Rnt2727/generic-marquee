<?php
/*
Plugin Name: Elementor Marquee Genérico
Description: Widget de Marquee personalizable para Elementor con items editables
Version: 1.0
Author: Tu Nombre
Text Domain: elementor-marquee-generico
*/

if (!defined('ABSPATH')) {
    exit; // Salir si se accede directamente
}

final class Elementor_Marquee_Generico {

    const VERSION = '1.0.0';
    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';
    const MINIMUM_PHP_VERSION = '7.0';

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct() {
        add_action('init', [$this, 'i18n']);
        add_action('plugins_loaded', [$this, 'init']);
    }

    public function i18n() {
        load_plugin_textdomain('elementor-marquee-generico');
    }

    public function init() {
        // Verificar si Elementor está instalado y activado
        if (!did_action('elementor/loaded')) {
            add_action('admin_notices', [$this, 'admin_notice_missing_elementor']);
            return;
        }

        // Verificar versión de Elementor
        if (!version_compare(ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_elementor_version']);
            return;
        }

        // Verificar versión de PHP
        if (version_compare(PHP_VERSION, self::MINIMUM_PHP_VERSION, '<')) {
            add_action('admin_notices', [$this, 'admin_notice_minimum_php_version']);
            return;
        }

        // Registrar widget
        add_action('elementor/widgets/widgets_registered', [$this, 'init_widgets']);
        
        // Registrar estilos y scripts
        add_action('elementor/frontend/after_enqueue_styles', [$this, 'enqueue_styles']);
        add_action('elementor/frontend/after_register_scripts', [$this, 'enqueue_scripts']);
    }

    public function admin_notice_missing_elementor() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requiere "%2$s" para funcionar.', 'elementor-marquee-generico'),
            '<strong>' . esc_html__('Elementor Marquee Genérico', 'elementor-marquee-generico') . '</strong>',
            '<strong>' . esc_html__('Elementor', 'elementor-marquee-generico') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_elementor_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requiere la versión "%2$s" o superior de "%3$s".', 'elementor-marquee-generico'),
            '<strong>' . esc_html__('Elementor Marquee Genérico', 'elementor-marquee-generico') . '</strong>',
            self::MINIMUM_ELEMENTOR_VERSION,
            '<strong>' . esc_html__('Elementor', 'elementor-marquee-generico') . '</strong>'
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function admin_notice_minimum_php_version() {
        if (isset($_GET['activate'])) unset($_GET['activate']);

        $message = sprintf(
            esc_html__('"%1$s" requiere la versión "%2$s" o superior de PHP.', 'elementor-marquee-generico'),
            '<strong>' . esc_html__('Elementor Marquee Genérico', 'elementor-marquee-generico') . '</strong>',
            self::MINIMUM_PHP_VERSION
        );

        printf('<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message);
    }

    public function init_widgets() {
        require_once(__DIR__ . '/widgets/marquee-widget.php');
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \Elementor_Marquee_Widget());
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'elementor-marquee-generico',
            plugins_url('/assets/css/style.css', __FILE__),
            [],
            self::VERSION
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            'elementor-marquee-generico',
            plugins_url('/assets/js/script.js', __FILE__),
            ['jquery'],
            self::VERSION,
            true
        );
    }
}

Elementor_Marquee_Generico::instance();

// Crear la carpeta de widgets si no existe
$widgets_dir = __DIR__ . '/widgets';
if (!file_exists($widgets_dir)) {
    wp_mkdir_p($widgets_dir);
}

// Crear el archivo del widget
$widget_file = $widgets_dir . '/marquee-widget.php';
if (!file_exists($widget_file)) {
    file_put_contents($widget_file, '<?php
class Elementor_Marquee_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return \'marquee_generico\';
    }

    public function get_title() {
        return __(\'Marquee Genérico\', \'elementor-marquee-generico\');
    }

    public function get_icon() {
        return \'eicon-slider-device\';
    }

    public function get_categories() {
        return [\'general\'];
    }

    protected function _register_controls() {
        $this->start_controls_section(
            \'content_section\',
            [
                \'label\' => __(\'Contenido\', \'elementor-marquee-generico\'),
                \'tab\' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new \Elementor\Repeater();

        $repeater->add_control(
            \'item_icon\',
            [
                \'label\' => __(\'Icono\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::TEXT,
                \'default\' => \'🌸\',
                \'placeholder\' => __(\'Ingrese un emoji o código de icono\', \'elementor-marquee-generico\'),
            ]
        );

        $repeater->add_control(
            \'item_text\',
            [
                \'label\' => __(\'Texto\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::TEXT,
                \'default\' => __(\'Item de ejemplo\', \'elementor-marquee-generico\'),
                \'placeholder\' => __(\'Ingrese el texto del item\', \'elementor-marquee-generico\'),
            ]
        );

        $repeater->add_control(
            \'item_link\',
            [
                \'label\' => __(\'Enlace\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::URL,
                \'placeholder\' => __(\'https://tu-enlace.com\', \'elementor-marquee-generico\'),
                \'show_external\' => true,
                \'default\' => [
                    \'url\' => \'\',
                    \'is_external\' => true,
                    \'nofollow\' => true,
                ],
            ]
        );

        $this->add_control(
            \'items\',
            [
                \'label\' => __(\'Items del Marquee\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::REPEATER,
                \'fields\' => $repeater->get_controls(),
                \'default\' => [
                    [
                        \'item_icon\' => \'🌸\',
                        \'item_text\' => __(\'Lifestyle & Fashion\', \'elementor-marquee-generico\'),
                    ],
                    [
                        \'item_icon\' => \'📊\',
                        \'item_text\' => __(\'Business\', \'elementor-marquee-generico\'),
                    ],
                    [
                        \'item_icon\' => \'📚\',
                        \'item_text\' => __(\'Foreign Languages\', \'elementor-marquee-generico\'),
                    ],
                ],
                \'title_field\' => \'{{{ item_text }}}\',
            ]
        );

        $this->end_controls_section();

        // Estilos
        $this->start_controls_section(
            \'style_section\',
            [
                \'label\' => __(\'Estilos\', \'elementor-marquee-generico\'),
                \'tab\' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            \'background_color\',
            [
                \'label\' => __(\'Color de fondo\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::COLOR,
                \'default\' => \'#f9fafb\',
                \'selectors\' => [
                    \'{{WRAPPER}} .marquee-container\' => \'background: {{VALUE}};\',
                    \'{{WRAPPER}} .marquee-container::before, {{WRAPPER}} .marquee-container::after\' => \'background: linear-gradient(90deg, {{VALUE}} 0%, transparent 100%);\',
                ],
            ]
        );

        $this->add_control(
            \'text_color\',
            [
                \'label\' => __(\'Color de texto\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::COLOR,
                \'default\' => \'#2d3748\',
                \'selectors\' => [
                    \'{{WRAPPER}} .mar-text\' => \'color: {{VALUE}};\',
                ],
            ]
        );

        $this->add_control(
            \'primary_color\',
            [
                \'label\' => __(\'Color primario\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::COLOR,
                \'default\' => \'#4f46e5\',
                \'selectors\' => [
                    \'{{WRAPPER}} .arrow-circle\' => \'background-color: {{VALUE}};\',
                    \'{{WRAPPER}} .jws-marquee .icon_text_bg .icon_text a:hover\' => \'color: {{VALUE}};\',
                ],
            ]
        );

        $this->add_control(
            \'primary_hover\',
            [
                \'label\' => __(\'Color primario (hover)\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::COLOR,
                \'default\' => \'#4338ca\',
                \'selectors\' => [
                    \'{{WRAPPER}} .jws-marquee .icon_text_bg .icon_text a:hover .arrow-circle\' => \'background-color: {{VALUE}};\',
                ],
            ]
        );

        $this->add_control(
            \'animation_speed\',
            [
                \'label\' => __(\'Velocidad de animación (segundos)\', \'elementor-marquee-generico\'),
                \'type\' => \Elementor\Controls_Manager::NUMBER,
                \'default\' => 20,
                \'min\' => 5,
                \'max\' => 60,
                \'step\' => 1,
                \'selectors\' => [
                    \'{{WRAPPER}} .marquee-wrapper\' => \'animation-duration: {{VALUE}}s;\',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings[\'items\'])) {
            return;
        }
        
        echo \'<div class="marquee-container jws-marquee">
            <div class="marquee-wrapper icon_text_bg">\';
            
        // Mostrar items originales
        foreach ($settings[\'items\'] as $item) {
            $this->render_item($item);
        }
        
        // Duplicar items para efecto de marquee continuo
        foreach ($settings[\'items\'] as $item) {
            $this->render_item($item);
        }
        
        echo \'</div></div>\';
    }
    
    protected function render_item($item) {
        $target = $item[\'item_link\'][\'is_external\'] ? \' target="_blank"\' : \'\';
        $nofollow = $item[\'item_link\'][\'nofollow\'] ? \' rel="nofollow"\' : \'\';
        
        echo \'<div class="item icon_text">
            <a href="\' . esc_url($item[\'item_link\'][\'url\']) . \'"\' . $target . $nofollow . \'>
                <span class="mar-icon">\' . esc_html($item[\'item_icon\']) . \'</span>
                <span class="mar-text">\' . esc_html($item[\'item_text\']) . \'</span>
                <span class="arrow-circle">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </span>
            </a>
        </div>\';
    }
}');
}

// Crear archivos de assets si no existen
$assets_dir = __DIR__ . '/assets';
if (!file_exists($assets_dir)) {
    wp_mkdir_p($assets_dir);
    wp_mkdir_p($assets_dir . '/css');
    wp_mkdir_p($assets_dir . '/js');
}

// Crear archivo CSS si no existe
$css_file = $assets_dir . '/css/style.css';
if (!file_exists($css_file)) {
    file_put_contents($css_file, ':root {
    --font2: \'Segoe UI\', Roboto, \'Helvetica Neue\', sans-serif;
    --heading: #2d3748;
    --primary: #4f46e5;
    --primary-hover: #4338ca;
}

.elementor-widget-marquee_generico .marquee-container {
    overflow: hidden;
    white-space: nowrap;
    position: relative;
    width: 100%;
    background: linear-gradient(90deg, #f9fafb 0%, #f3f4f6 100%);
    padding: 16px 0;
}

.elementor-widget-marquee_generico .marquee-wrapper {
    display: flex;
    width: max-content;
    animation: marquee 20s linear infinite;
}

.elementor-widget-marquee_generico .item {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    padding: 0 8px;
}

.elementor-widget-marquee_generico .jws-marquee .icon_text_bg .icon_text a {
    background-color: #FFF;
    box-shadow: 0 4px 12px rgba(16,30,87,0.08);
    border-radius: 50px;
    padding: 4px 16px;
    display: flex;
    align-items: center;
    font-weight: 600;
    font-family: var(--font2);
    color: var(--heading);
    text-decoration: none;
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    height: 48px;
}

.elementor-widget-marquee_generico .jws-marquee .icon_text_bg .icon_text a:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(16,30,87,0.12);
    color: var(--primary);
}

.elementor-widget-marquee_generico .mar-icon {
    margin-right: 12px;
    font-size: 20px;
    transition: transform 0.3s ease;
}

.elementor-widget-marquee_generico .jws-marquee .icon_text_bg .icon_text a:hover .mar-icon {
    transform: scale(1.1);
}

.elementor-widget-marquee_generico .mar-text {
    font-size: 14px;
    margin-right: 8px;
}

.elementor-widget-marquee_generico .arrow-circle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    background-color: var(--primary);
    color: white;
    border-radius: 50%;
    margin-left: 4px;
    transition: all 0.3s ease;
}

.elementor-widget-marquee_generico .arrow-circle svg {
    width: 10px;
    height: 10px;
}

.elementor-widget-marquee_generico .jws-marquee .icon_text_bg .icon_text a:hover .arrow-circle {
    background-color: var(--primary-hover);
    transform: scale(1.1);
}

@keyframes marquee {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.elementor-widget-marquee_generico .marquee-container::before,
.elementor-widget-marquee_generico .marquee-container::after {
    content: \'\';
    position: absolute;
    top: 0;
    bottom: 0;
    width: 100px;
    z-index: 2;
    pointer-events: none;
}

.elementor-widget-marquee_generico .marquee-container::before {
    left: 0;
    background: linear-gradient(90deg, #f9fafb 0%, transparent 100%);
}

.elementor-widget-marquee_generico .marquee-container::after {
    right: 0;
    background: linear-gradient(90deg, transparent 0%, #f9fafb 100%);
}');
}

// Crear archivo JS si no existe
$js_file = $assets_dir . '/js/script.js';
if (!file_exists($js_file)) {
    file_put_contents($js_file, '// Puedes añadir funcionalidad JavaScript aquí si es necesario
console.log(\'Marquee Genérico cargado\');');
}