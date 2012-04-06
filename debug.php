<?php 

class WP_Skeleton_Logger {
    static $instance = null;

    private function WP_Skeleton_Logger() {
        $this->html = true;
        $this->from = true;
        $this->context = 1;

        $this->root = realpath(getenv('DOCUMENT_ROOT'));

        $this->log = array();

        add_action('admin_footer', array($this, 'show'));
        add_action('wp_footer', array($this, 'show'));
    }

    public function instance() {
        if (is_null(WP_Skeleton_Logger::$instance)) {
            WP_Skeleton_Logger::$instance = new WP_Skeleton_Logger();
        }
        return WP_Skeleton_Logger::$instance;
    }

    public function log($var, $type='debug', $print=false) {
        $entry = array('backtrace' => debug_backtrace(), 'var' => $var, 'type' => $type);
        $this->log[] = $entry;
        return $print ? $this->render($entry) : true;
    }

    public function debug($vars, $print=false) {
        if (count($vars) > 1) {
            return $this->log($vars, 'debug', $print);
        } else {
            return $this->log($vars[0], 'debug', $print);
        }
    }

    public function render($entry) {
        $var = $entry['var'];
        $backtrace = $entry['backtrace'];

        $start = 2;
        $limit = $this->context + $start;

        $html = '<div class="' . $entry['type'] . '">';
        if ($this->from) {
            $items = array();
            for ($k = $start; $k < $limit; $k++) {
                if (!isset($backtrace[$k])) {
                    break;
                }

                $item = '<strong>';
                $item .= substr(str_replace($this->root, '', $backtrace[$k]['file']), 1);
                $item .= ':' . $backtrace[$k]['line'];
                $item .= ' - function <strong>' . $backtrace[$k]['function'] . '</strong>()';
                $item .= '</strong>';

                $items[] = $item;
            }
            $html .= join('<br/>', $items);
        }

        $var = print_r($var, true);
        if ($this->html && !empty($var)) {
            $html .= "\n<pre class=\"cake-debug\" style=\"color:#000; background: #FFF\">\n";
            $var = str_replace('<', '&lt;', str_replace('>', '&gt;', $var));
            $html .= $var . "\n</pre>\n";
        } else {
            $html .= '<br/>';
        }

        $html = $html . '</div>';

        return $html;
    }

    public function show() {
        if (!file_exists(AWPCP_DIR . '/DEBUG')) {
            return;
        }

        if (empty($this->log)) {
            return;
        }

        $html = '';
        foreach($this->log as $entry) {
            $html .= $this->render($entry);
        }

        echo '<div style="background:#000; color: #FFF; padding-bottom: 40px">' . $html . '</div>';
    }
}

if (!function_exists('debug')) {
    function debugp($var = false) {
        $args = func_get_args();
        echo WP_Skeleton_Logger::instance()->debug($args, true);
    }

    function debug($var = false) {
        $args = func_get_args();
        return WP_Skeleton_Logger::instance()->debug($args, false);
    }
}

// how to find debug calls
// ^[^/\n]+debugp?\(