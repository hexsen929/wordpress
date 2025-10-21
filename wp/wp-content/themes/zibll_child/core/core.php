<?php

// 获取插件菜单设置
// 例如：菜单id为 aut_url
// 则通过：_mrhe('aut_url') 获取这里的内容
// if (!function_exists('_mrhe')) {
//     function _mrhe($option = '', $default = null) {
//         $options = get_option('mrhe_options');
//         return (isset($options[$option])) ? $options[$option] : $default;
//     }
// }

// 获取及设置主题配置参数
if (!function_exists('_mrhe')) {
	function _mrhe($name, $default = false, $subname = '') {
		//声明静态变量，加速获取
		static $options = null;
		if ($options === null) {
			$options = get_option('mrhe_options');
		}

		if (isset($options[$name])) {
			if ($subname) {
				return isset($options[$name][$subname]) ? $options[$name][$subname] : $default;
			} else {
				return $options[$name];
			}
		}
		return $default;
	}
}

// 载入文件，这是子比主题引入文件的函数调用示例，详见父主题目录下的inc/inc.php第80行zib_require()函数
// 注意：授权系统已通过 functions.php 中的 loader.php 加载，不需要在此重复引用
// 注意：mrhecode 相关文件已通过 mrhecode/functions.php 统一加载，不需要在此重复引用
zib_require(array(
    'core/options/options', // 配置文件
    'core/functions/functions', // 功能函数
    'mrhecode/functions', // mrhecode 功能文件（必须在 core.php 之后，因为需要 _mrhe() 函数）
), true);
