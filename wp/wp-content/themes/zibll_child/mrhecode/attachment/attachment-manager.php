<?php
/**
 * 前端附件管理功能
 * 移植自 zib-uploads 插件
 */

if (!defined('ABSPATH')) {
    exit;
}

// 获取文件类型SVG图标函数
function mrhe_attachment_get_file_type_icon($mime_type) {
    $svg_icons = array(
        'application/zip' => '<svg t="1760625020221" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1667" width="200" height="200"><path d="M923.136 969.557333H193.024v-909.653333h521.386667l208.725333 207.701333z" fill="#7CCDFF" p-id="1668"></path><path d="M379.050667 68.096h72.021333v87.722667h-72.021333zM459.093333 166.570667h57.856V254.293333H459.093333zM394.069333 265.045333h57.856v87.722667h-57.856zM459.093333 363.349333h57.856v87.722667H459.093333zM394.069333 461.824h57.856V549.546667h-57.856z" fill="#4191FB" p-id="1669"></path><path d="M577.365333 756.736l-40.618666-174.250667h-0.170667c-3.072-46.933333-38.4-84.138667-81.578667-84.138666-44.373333 0-80.384 38.912-81.749333 87.552h-0.170667l-41.813333 175.616v0.170666c-1.706667 6.485333-2.56 13.141333-2.56 20.138667 0 55.466667 56.490667 100.522667 126.293333 100.522667s126.293333-45.056 126.293334-100.522667c0-8.874667-1.365333-17.237333-3.925334-25.088z m-122.368 49.834667c-36.181333 0-65.536-17.578667-65.536-39.253334s29.354667-39.253333 65.536-39.253333 65.536 17.578667 65.536 39.253333-29.354667 39.253333-65.536 39.253334z" fill="#4191FB" p-id="1670"></path><path d="M912.896 253.952l2.56 671.914667c0 15.530667-12.458667 27.989333-27.989333 27.989333H237.909333c-15.530667 0-27.989333-12.458667-27.989333-27.989333V96.938667c0-15.530667 12.458667-27.989333 27.989333-27.989334l493.397334-1.024-38.912-39.936H239.274667c-36.352 0-65.706667 29.354667-65.706667 65.706667v835.584c0 36.352 29.354667 65.706667 65.706667 65.706667h646.826666c36.352 0 65.706667-29.354667 65.706667-65.706667V293.888l-38.912-39.936z" fill="#4191FB" p-id="1671"></path><path d="M692.394667 222.72c0 39.424 31.914667 71.338667 71.338666 71.338667h188.245334L692.394667 27.989333v194.730667z" fill="#C7E2FF" p-id="1672"></path><path d="M557.568 482.304H158.72c-50.346667 0-91.136-40.789333-91.136-91.136v-60.245333c0-50.346667 40.789333-91.136 91.136-91.136h398.848c50.346667 0 91.136 40.789333 91.136 91.136v60.245333c0 50.346667-40.789333 91.136-91.136 91.136z" fill="#4191FB" p-id="1673"></path><path d="M174.933333 287.573333h136.192v31.061334l-87.381333 91.136h90.624v33.450666H166.570667v-32.256l86.528-90.112h-78.165334v-33.28zM337.92 287.573333h48.298667v155.818667H337.92v-155.818667zM420.522667 287.573333h80.042666c17.408 0 30.549333 4.096 39.082667 12.458667 8.704 8.362667 12.970667 20.138667 12.970667 35.328 0 15.701333-4.778667 27.989333-14.165334 36.864-9.386667 8.874667-23.893333 13.312-43.349333 13.312h-26.282667v57.856h-48.298666v-155.818667z m48.298666 66.389334h11.776c9.216 0 15.872-1.536 19.626667-4.778667 3.754667-3.242667 5.632-7.338667 5.632-12.458667 0-4.949333-1.706667-9.045333-4.949333-12.458666s-9.386667-5.12-18.432-5.12h-13.653334v34.816z" fill="#FFFFFF" p-id="1674"></path></svg>',
        'application/x-rar-compressed' => '<svg t="1760625037092" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1823" width="200" height="200"><path d="M923.136 969.557333H193.024v-909.653333h521.386667l208.725333 207.701333z" fill="#7CCDFF" p-id="1824"></path><path d="M474.282667 52.053333l-7.168 7.850667-128.170667 140.458667 135.338667-16.896-135.338667 235.861333 116.224 15.189333L350.890667 648.533333l107.349333-8.362666-157.354667 232.277333 135.338667-30.378667-135.338667 127.488 278.016-185.685333-185.344 18.090667 135.338667-240.981334-110.762667 30.890667 91.818667-197.802667-95.061333-13.482666L517.12 151.552l-99.84 9.386667z" fill="#4191FB" p-id="1825"></path><path d="M912.896 253.952l2.56 671.914667c0 15.530667-12.458667 27.989333-27.989333 27.989333H237.909333c-15.530667 0-27.989333-12.458667-27.989333-27.989333V96.938667c0-15.530667 12.458667-27.989333 27.989333-27.989334l493.397334-1.024-38.912-39.936H239.274667c-36.352 0-65.706667 29.354667-65.706667 65.706667v835.584c0 36.352 29.354667 65.706667 65.706667 65.706667h646.826666c36.352 0 65.706667-29.354667 65.706667-65.706667V293.888l-38.912-39.936z" fill="#4191FB" p-id="1826"></path><path d="M692.394667 222.72c0 39.424 31.914667 71.338667 71.338666 71.338667h188.245334L692.394667 27.989333v194.730667z" fill="#C7E2FF" p-id="1827"></path><path d="M557.568 482.304H158.72c-50.346667 0-91.136-40.789333-91.136-91.136v-60.245333c0-50.346667 40.789333-91.136 91.136-91.136h398.848c50.346667 0 91.136 40.789333 91.136 91.136v60.245333c0 50.346667-40.789333 91.136-91.136 91.136z" fill="#4191FB" p-id="1828"></path><path d="M121.856 443.221333v-155.818666h80.213333c14.848 0 26.282667 1.194667 34.133334 3.754666 7.850667 2.56 14.165333 7.338667 18.944 14.165334s7.168 15.36 7.168 25.258666c0 8.704-1.877333 16.042667-5.461334 22.357334-3.754667 6.314667-8.704 11.434667-15.189333 15.189333-4.096 2.56-9.728 4.608-16.896 6.144 5.802667 1.877333 9.898667 3.754667 12.458667 5.802667 1.706667 1.194667 4.266667 3.925333 7.68 8.192s5.632 7.338667 6.656 9.728l23.381333 45.056h-54.442667l-25.770666-47.616c-3.242667-6.144-6.144-10.24-8.704-11.946667-3.413333-2.389333-7.338667-3.584-11.776-3.584h-4.266667v63.146667H121.856v0.170666z m48.298667-92.501333h20.309333c2.218667 0 6.485333-0.682667 12.8-2.048 3.242667-0.682667 5.802667-2.218667 7.850667-4.949333 2.048-2.56 3.072-5.632 3.072-9.045334 0-4.949333-1.536-8.874667-4.778667-11.605333s-9.216-4.096-17.92-4.096H170.154667v31.744z" fill="#FFFFFF" p-id="1829"></path><path d="M386.218667 417.621333h-54.613334l-7.509333 25.770667H274.773333l58.538667-155.818667h52.565333l58.538667 155.818667h-50.346667l-7.850666-25.770667z m-10.069334-33.792l-17.237333-55.978666-17.066667 55.978666h34.304zM460.288 443.221333v-155.818666h80.213333c14.848 0 26.282667 1.194667 34.133334 3.754666s14.165333 7.338667 18.944 14.165334c4.778667 6.826667 7.168 15.36 7.168 25.258666 0 8.704-1.877333 16.042667-5.461334 22.357334-3.754667 6.314667-8.704 11.434667-15.189333 15.189333-4.096 2.56-9.728 4.608-16.896 6.144 5.802667 1.877333 9.898667 3.754667 12.458667 5.802667 1.706667 1.194667 4.266667 3.925333 7.68 8.192s5.632 7.338667 6.656 9.728l23.381333 45.056h-54.442667l-25.770666-47.616c-3.242667-6.144-6.144-10.24-8.704-11.946667-3.413333-2.389333-7.338667-3.584-11.776-3.584H508.586667v63.146667h-48.298667v0.170666z m48.298667-92.501333h20.309333c2.218667 0 6.485333-0.682667 12.8-2.048 3.242667-0.682667 5.802667-2.218667 7.850667-4.949333 2.048-2.56 3.072-5.632 3.072-9.045334 0-4.949333-1.536-8.874667-4.778667-11.605333s-9.216-4.096-17.92-4.096H508.586667v31.744z" fill="#FFFFFF" p-id="1830"></path></svg>',
        'audio/' => '<svg t="1760625180545" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2134" width="200" height="200"><path d="M923.136 969.557333H193.024v-909.653333h521.386667l208.725333 207.701333z" fill="#7CCDFF" p-id="2135"></path><path d="M912.896 253.952l2.56 671.914667c0 15.530667-12.458667 27.989333-27.989333 27.989333H237.909333c-15.530667 0-27.989333-12.458667-27.989333-27.989333V96.938667c0-15.530667 12.458667-27.989333 27.989333-27.989334l493.397334-1.024-38.912-39.936H239.274667c-36.352 0-65.706667 29.354667-65.706667 65.706667v835.584c0 36.352 29.354667 65.706667 65.706667 65.706667h646.826666c36.352 0 65.706667-29.354667 65.706667-65.706667V293.888l-38.912-39.936z" fill="#4191FB" p-id="2136"></path><path d="M692.394667 222.72c0 39.424 31.914667 71.338667 71.338666 71.338667h188.245334L692.394667 27.989333v194.730667z" fill="#C7E2FF" p-id="2137"></path><path d="M557.568 482.304H158.72c-50.346667 0-91.136-40.789333-91.136-91.136v-60.245333c0-50.346667 40.789333-91.136 91.136-91.136h398.848c50.346667 0 91.136 40.789333 91.136 91.136v60.245333c0 50.346667-40.789333 91.136-91.136 91.136z" fill="#4191FB" p-id="2138"></path><path d="M97.621333 287.573333h39.424l14.165334 87.210667 20.821333-87.210667h39.253333l20.821334 87.04 14.165333-87.04h39.253333L256 443.221333h-40.789333l-23.552-98.133333-23.552 98.133333H127.488l-29.866667-155.648zM298.837333 287.573333h54.613334l20.992 94.72 20.992-94.72h54.613333v155.818667h-33.962667v-118.784l-26.282666 118.784h-30.72L332.8 324.608v118.784h-33.962667v-155.818667zM463.36 287.573333h43.349333l30.208 112.128 29.866667-112.128h42.154667l-50.005334 155.818667h-45.056l-50.517333-155.818667z" fill="#FFFFFF" p-id="2139"></path><path d="M385.536 575.317333h-62.122667c-3.413333 0-6.144 2.730667-6.144 6.144v62.122667c0 3.413333 2.730667 6.144 6.144 6.144h62.122667c3.413333 0 6.144-2.730667 6.144-6.144v-62.122667c0-3.413333-2.901333-6.144-6.144-6.144zM385.536 680.618667h-62.122667c-3.413333 0-6.144 2.730667-6.144 6.144v62.122666c0 3.413333 2.730667 6.144 6.144 6.144h62.122667c3.413333 0 6.144-2.730667 6.144-6.144v-62.122666c0-3.413333-2.901333-6.144-6.144-6.144zM385.536 785.749333h-62.122667c-3.413333 0-6.144 2.730667-6.144 6.144v62.122667c0 3.413333 2.730667 6.144 6.144 6.144h62.122667c3.413333 0 6.144-2.730667 6.144-6.144V791.893333c0-3.413333-2.901333-6.144-6.144-6.144zM802.986667 575.317333h-62.122667c-3.413333 0-6.144 2.730667-6.144 6.144v62.122667c0 3.413333 2.730667 6.144 6.144 6.144h62.122667c3.413333 0 6.144-2.730667 6.144-6.144v-62.122667c0.170667-3.413333-2.730667-6.144-6.144-6.144zM802.986667 680.618667h-62.122667c-3.413333 0-6.144 2.730667-6.144 6.144v62.122666c0 3.413333 2.730667 6.144 6.144 6.144h62.122667c3.413333 0 6.144-2.730667 6.144-6.144v-62.122666c0.170667-3.413333-2.730667-6.144-6.144-6.144zM802.986667 785.749333h-62.122667c-3.413333 0-6.144 2.730667-6.144 6.144v62.122667c0 3.413333 2.730667 6.144 6.144 6.144h62.122667c3.413333 0 6.144-2.730667 6.144-6.144V791.893333c0.170667-3.413333-2.730667-6.144-6.144-6.144zM709.632 575.317333H416.768c-3.413333 0-6.144 2.730667-6.144 6.144v272.554667c0 3.413333 2.730667 6.144 6.144 6.144h293.034667c3.413333 0 6.144-2.730667 6.144-6.144V581.461333c0-3.413333-2.901333-6.144-6.314667-6.144z m-102.4 155.989334L541.013333 797.525333c-4.437333 4.437333-11.434667 4.437333-15.872 0v-148.138666c4.437333-4.437333 11.434667-4.437333 15.872 0l66.218667 66.218666c4.266667 4.266667 4.266667 11.434667 0 15.701334z" fill="#4191FB" p-id="2140"></path></svg>',
        'video/' => '<svg t="1760625078251" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="1979" width="200" height="200"><path d="M923.136 969.557333H193.024v-909.653333h521.386667l208.725333 207.701333z" fill="#7CCDFF" p-id="1980"></path><path d="M912.896 253.952l2.56 671.914667c0 15.530667-12.458667 27.989333-27.989333 27.989333H237.909333c-15.530667 0-27.989333-12.458667-27.989333-27.989333V96.938667c0-15.530667 12.458667-27.989333 27.989333-27.989334l493.397334-1.024-38.912-39.936H239.274667c-36.352 0-65.706667 29.354667-65.706667 65.706667v835.584c0 36.352 29.354667 65.706667 65.706667 65.706667h646.826666c36.352 0 65.706667-29.354667 65.706667-65.706667V293.888l-38.912-39.936z" fill="#4191FB" p-id="1981"></path><path d="M692.394667 222.72c0 39.424 31.914667 71.338667 71.338666 71.338667h188.245334L692.394667 27.989333v194.730667z" fill="#C7E2FF" p-id="1982"></path><path d="M557.568 482.304H158.72c-50.346667 0-91.136-40.789333-91.136-91.136v-60.245333c0-50.346667 40.789333-91.136 91.136-91.136h398.848c50.346667 0 91.136 40.789333 91.136 91.136v60.245333c0 50.346667-40.789333 91.136-91.136 91.136z" fill="#4191FB" p-id="1983"></path><path d="M107.861333 287.573333h61.44L192.853333 382.293333l23.552-94.72h61.269334v155.818667h-38.229334v-118.784L209.92 443.221333h-34.645333l-29.354667-118.784v118.784H107.861333v-155.648zM301.568 365.568c0-25.429333 6.826667-45.226667 20.650667-59.392 13.653333-14.165333 32.938667-21.162667 57.344-21.162667 25.088 0 44.544 6.997333 58.026666 20.821334 13.653333 13.994667 20.48 33.450667 20.48 58.538666 0 18.261333-2.901333 33.109333-8.874666 44.714667s-14.506667 20.650667-25.770667 27.136c-11.264 6.485333-25.258667 9.728-41.984 9.728-17.066667 0-31.061333-2.730667-42.325333-8.362667-11.093333-5.632-20.138667-14.506667-27.136-26.624s-10.410667-27.306667-10.410667-45.397333z m46.762667 0.170667c0 15.701333 2.901333 26.965333 8.533333 33.962666 5.632 6.826667 13.312 10.24 23.210667 10.24 10.069333 0 17.749333-3.413333 23.210666-10.069333 5.461333-6.656 8.192-18.773333 8.192-36.181333 0-14.677333-2.901333-25.429333-8.533333-32.085334-5.802667-6.826667-13.482667-10.069333-23.381333-10.069333-9.386667 0-16.896 3.413333-22.698667 10.24-5.802667 6.656-8.533333 18.090667-8.533333 33.962667zM459.264 287.573333h48.810667l33.962666 112.128 33.621334-112.128h47.445333l-56.149333 155.818667h-50.517334l-57.173333-155.818667z" fill="#FFFFFF" p-id="1984"></path><path d="M704.170667 887.296c-17.237333 4.608-28.330667-4.608-38.912-16.384-11.605333-12.970667-12.117333-12.458667-27.306667-4.778667-59.733333 30.378667-119.125333 29.866667-175.957333-5.461333-51.882667-32.256-80.213333-80.725333-81.066667-142.165333-1.194667-74.922667 33.621333-129.536 100.352-162.816 61.098667-30.549333 141.994667-16.896 191.317333 30.378666 39.936 38.229333 58.026667 84.821333 55.125334 139.605334-1.536 27.818667-9.898667 54.101333-24.576 77.824-4.096 6.656-3.584 11.093333 2.389333 15.36 19.285333 13.312 25.6 31.573333 22.869333 54.613333-1.706667 13.994667-0.341333 13.994667-14.677333 14.165333-3.242667-0.341333-6.314667-0.341333-9.557333-0.341333z m-55.978667-229.376c-2.218667 1.877333-4.437333 3.754667-6.485333 5.802667-21.333333 21.674667-42.496 43.349333-63.829334 65.024-7.509333 7.68-16.384 12.288-27.306666 10.069333-10.922667-2.218667-18.944-8.874667-22.357334-19.626667-3.242667-10.24 0-19.285333 7.509334-26.794666 21.674667-21.674667 43.349333-43.349333 65.194666-65.024 6.997333-6.997333 6.997333-6.997333-2.048-11.434667-10.410667-5.12-21.674667-8.362667-33.109333-9.728-47.786667-5.12-93.354667 22.357333-111.104 66.389333-18.090667 45.056-4.608 96.426667 33.28 125.952 38.4 29.866667 93.525333 30.037333 132.437333 0.341334 38.058667-29.184 52.224-82.944 33.450667-127.488-2.048-4.437333-3.925333-9.045333-5.632-13.482667 8.704-6.656 14.506667-16.213333 22.698667-23.722667 3.584-3.242667 1.365333-6.314667-0.682667-9.216-7.509333-10.410667-16.725333-18.944-26.794667-26.794666-3.413333-2.56-6.314667-2.389333-9.216 0.341333-6.314667 5.461333-12.458667 11.093333-18.773333 16.725333-3.242667 2.901333-3.584 5.632 0.512 8.362667 13.141333 9.045333 23.552 20.821333 32.256 34.304z" fill="#4191FB" p-id="1985"></path></svg>',
        'image/' => '<svg t="1760680354339" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="10471" width="200" height="200"><path d="M868.751 199.844H155.25a44.595 44.595 0 0 0-44.596 44.595v431.068h802.688V244.44a44.595 44.595 0 0 0-44.59-44.595z" fill="#98DCF0" p-id="10472"></path><path d="M609.884 496.988l-154.946 178.52h309.867l-154.92-178.52z" fill="#699B54" p-id="10473"></path><path d="M583.066 675.512L376.53 437.535 169.99 675.512h-59.337v104.044a44.595 44.595 0 0 0 44.596 44.6h713.497c24.627 0 44.595-19.968 44.595-44.6V675.507l-330.275 0.005z" fill="#80BB67" p-id="10474"></path><path d="M705.234 348.488c-0.015 32.834 26.593 59.463 59.433 59.479 32.834 0.01 59.464-26.599 59.479-59.438v-0.041c0.01-32.84-26.598-59.47-59.433-59.48-32.84-0.01-59.469 26.599-59.48 59.434v0.046z" fill="#FFE68E" p-id="10475"></path></svg>',
    );
    
    // 允许通过过滤器添加或修改SVG图标
    $svg_icons = apply_filters('zibll_plugin_svg_icons', $svg_icons);
    
    // 查找匹配的SVG图标
    foreach ($svg_icons as $key => $svg) {
        if (strpos($mime_type, $key) === 0) {
            return $svg;
        }
    }
    
    // 默认SVG图标
    return '<svg t="1760626445291" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="6493" width="200" height="200"><path d="M0 128a51.2 51.2 0 0 1 51.2-51.2h350.24a51.2 51.2 0 0 1 47.0592 31.0336L473.6 166.4h499.2a51.2 51.2 0 0 1 51.2 51.2v537.6a51.2 51.2 0 0 1-51.2 51.2H51.2a51.2 51.2 0 0 1-51.2-51.2V128z" fill="#FFA000" p-id="6494"></path><path d="M89.6 249.6m51.2 0l742.4 0q51.2 0 51.2 51.2l0 460.8q0 51.2-51.2 51.2l-742.4 0q-51.2 0-51.2-51.2l0-460.8q0-51.2 51.2-51.2Z" fill="#FFFFFF" p-id="6495"></path><path d="M0 332.8m51.2 0l921.6 0q51.2 0 51.2 51.2l0 512q0 51.2-51.2 51.2l-921.6 0q-51.2 0-51.2-51.2l0-512q0-51.2 51.2-51.2Z" fill="#FFCA28" p-id="6496"></path></svg>';
}

// 挂钩用户中心的tab内容
function mrhe_attachment_user_page_tab_content($user_id = '', $paged = 1, $posts_per_page = 16) {
    if (!$user_id) $user_id = get_current_user_id();
    if (!$user_id) return;

    // 准备查询参数
    $paged = isset($_REQUEST['paged']) ? (int)$_REQUEST['paged'] : (int)$paged;
    $posts_per_page = isset($_REQUEST['posts_per_page']) ? (int)$_REQUEST['posts_per_page'] : (int)$posts_per_page;
    $offset = ($paged - 1) * $posts_per_page;

    // 设置参数以获取特定用户上传的所有类型媒体文件
    $args = array(
        'post_type'      => 'attachment',
        'posts_per_page' => $posts_per_page,
        'offset'         => $offset,
        'author'         => $user_id,
        'post_status'    => 'inherit',
    );

    // 使用 WP_Query 查询
    $query = new WP_Query($args);
    $html  = '';

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $post;

            // 获取文件信息
            $file_url = wp_get_attachment_url(get_the_ID());
            $mime_type = get_post_mime_type(get_the_ID());
            
            // 判断文件类型并设置相应的预览方式
            if (strpos($mime_type, 'image/') === 0) {
                // 图片文件 - 显示缩略图
                $full_url = wp_get_attachment_image_src(get_the_ID(), 'full');
                $preview_img = '<img src="' . esc_url($full_url[0]) . '" data-src="' . esc_url($full_url[0]) . '" alt="' . esc_attr(get_the_title()) . '" class="fit-cover radius8 lazyloadafter ls-is-cached lazyloaded" loading="lazy" imgbox-index="0">';
            } else {
                // 非图片文件 - 显示SVG图标
                $svg_icon = mrhe_attachment_get_file_type_icon($mime_type);
                $preview_img = '<div class="file-type-icon" style="display: flex; justify-content: center; align-items: center; font-size:50px;margin: 20%;">';
                $preview_img .= $svg_icon;
                $preview_img .= '</div>';
            }

            // 构建HTML结构
            $html .= '<posts class="posts-item card ajax-item">';
            $html .= '<div class="item-thumbnail imgbox-container">';
            $html .= $preview_img;
            $html .= '<div class="but-average" style="z-index: 1;position: absolute;bottom: 0;width: 100%;">';
            $html .= mrhe_attachment_view_link(get_the_ID(), 'but c-blue', ' ' . zib_get_svg('view') . '查看', 'a');
            if (_mrhe('attachment_delete_enabled')) {
                $html .= mrhe_attachment_delete_link(get_the_ID(), 'but c-red', '<i class="fa fa-trash-o" aria-hidden="true"></i>删除', 'a');
            }
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="item-content mt6">';
            $html .= '<div class="item-title text-ellipsis">' . esc_html(get_the_title()) . '</div>';
            $html .= '<div class="em09 muted-2-color">' . mrhe_attachment_format_file_size(get_the_ID()) . '</div>';
            $html .= '</div>';
            $html .= '</posts>';
        }
        wp_reset_postdata();
    } else {
        $html .= zib_get_ajax_null('暂无文件', 40, 'null-order.svg');
    }
    
    global $wpdb;
    // 计算总数量和分页链接
    $total_items = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_author = %d AND post_status = 'inherit'",
            $user_id
        )
    );
    $ajax_url = esc_url(add_query_arg('action', 'mrhe_attachment_ajax', admin_url('admin-ajax.php')));

    if (_mrhe('paging_ajax_s', '1') === '1') {
        $html .= zib_get_ajax_next_paginate($total_items, $paged, $posts_per_page, $ajax_url);
    } else {
        $html .= zib_get_ajax_number_paginate($total_items, $paged, $posts_per_page, $ajax_url);
    }

    return zib_get_ajax_ajaxpager_one_centent($html);
}
add_filter('main_user_tab_content_imgmanage', 'mrhe_attachment_user_page_tab_content');

// 格式化文件大小函数
function mrhe_attachment_format_file_size($file_or_id) {
    if (empty($file_or_id)) {
        return '未知大小';
    }
    
    $bytes = false;
    
    // 判断参数类型：数字=ID，字符串=路径
    if (is_numeric($file_or_id)) {
        $attachment_id = intval($file_or_id);
    } else {
        // 兼容旧的路径调用：从路径查找ID
        global $wpdb;
        $file_pattern = '%' . $wpdb->esc_like(basename($file_or_id)) . '%';
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
            $file_pattern
        ));
        
        // 如果找不到ID，尝试本地文件
        if (!$attachment_id && file_exists($file_or_id)) {
            $bytes = filesize($file_or_id);
        }
    }
    
    // 优先级1: 从 WordPress metadata 读取
    if ($attachment_id && $bytes === false) {
        $metadata = wp_get_attachment_metadata($attachment_id);
        if (isset($metadata['filesize'])) {
            $bytes = $metadata['filesize'];
        }
    }
    
    // 优先级2: 尝试本地文件（兼容未启用云存储）
    if ($attachment_id && $bytes === false) {
        $file_path = get_attached_file($attachment_id);
        if ($file_path && file_exists($file_path)) {
            $bytes = filesize($file_path);
        }
    }
    
    // 所有方法都失败
    if ($bytes === false || $bytes === 0) {
        return '未知大小';
    }
    
    // 格式化文件大小
    $sizes = array('B', 'KB', 'MB', 'GB', 'TB');
    $i = floor(log($bytes, 1024));
    return round($bytes / pow(1024, $i), 2) . ' ' . $sizes[$i];
}

// 注册 AJAX 回调
function mrhe_attachment_ajax() {
    // 检查功能是否启用
    if (!_mrhe('attachment_manager_s')) {
        wp_send_json_error('功能未启用');
        exit;
    }
    
    // 加载动画
    $ajax_loader = '<div class="mt10"> <i class="placeholder s1" style="width: 40%;height: 30px;"></i><i class="placeholder s1 ml10" style="width: 50%;height: 30px;"></i></div>';
    $ajax_loader = '<span class="post_ajax_loader" style="display: none;">' . $ajax_loader . $ajax_loader . $ajax_loader . '</span>';

    // 获取并构建文件列表内容
    $con = mrhe_attachment_user_page_tab_content(
        isset($_POST['user_id']) ? (int)$_POST['user_id'] : '',
        isset($_POST['paged']) ? (int)$_POST['paged'] : 1,
        isset($_POST['posts_per_page']) ? (int)$_POST['posts_per_page'] : (_mrhe('attachment_list_number') ?: 16)
    );

    // 构建响应内容
    $response = '<body><main><div class="ajaxpager">';
    $response .= $con;
    $response .= $ajax_loader;
    $response .= '<div class="ajax-pag hide"><div class="next-page ajax-next"><a href="#"></a></div></div>';
    $response .= '</div></main></body>';

    // 输出响应并结束脚本执行
    echo $response;
    exit;
}
add_action('wp_ajax_mrhe_attachment_ajax', 'mrhe_attachment_ajax');

function mrhe_attachment_ajax_delete_modal() {
    // 检查删除功能是否启用
    if (!_mrhe('attachment_delete_enabled')) {
        zib_ajax_notice_modal('danger', '删除功能已关闭');
    }
    
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

    if (!$id) {
        zib_ajax_notice_modal('danger', '参数传入错误');
    }

    // 获取附件对象
    $attachment = get_post($id);

    if (empty($attachment->ID) || 'attachment' !== $attachment->post_type) {
        zib_ajax_notice_modal('danger', '附件不存在或参数传入错误');
    }

    // 检查用户是否有权限删除该附件
    if (!current_user_can('delete_post', $id)) {
        zib_ajax_notice_modal('danger', '您没有删除此附件的权限');
    }

    // 创建隐藏的 nonce 字段
    $nonce_field = wp_nonce_field('zibll_plugin_delete_attachment', '_wpnonce', true, false);

    // 准备HTML输出
    $html = '<form class="plate-delete-form">';
    $html .= '<div class="modal-colorful-header colorful-bg jb-red">';
    $html .= '<button class="close" data-dismiss="modal"><svg class="ic-close" aria-hidden="true"><use xlink:href="#icon-close"></use></svg></button>';
    $html .= '<div class="colorful-make"></div>';
    $html .= '<div class="text-center">';
    $html .= '<div class="em2x"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i></div>';
    $html .= '<div class="mt10 em12 padding-w10">确认删除此附件？</div>';
    $html .= '</div>';
    $html .= '</div>';
    $html .= '<div>';
    $html .= '<div class="em12 mb10">您正在删除附件<b>' . esc_html($attachment->post_title) . '</b></div>';
    $html .= '<div class="c-red mb20">确认要删除吗？</div>';
    $html .= '<div class="mt20 but-average">';
    $html .= '<input type="hidden" name="action" value="zibll_plugin_delete_attachment">';
    $html .= '<input type="hidden" name="id" value="' . esc_attr($id) . '">';
    $html .= $nonce_field;
    $html .= '<button type="button" data-dismiss="modal" href="javascript:;" class="but">取消</button>';
    $html .= '<button type="submit" class="but c-red wp-ajax-submit"><i class="fa fa-trash-o" aria-hidden="true"></i>确认删除</button>';
    $html .= '</div></div></form>';

    echo $html;
    exit;
}
add_action('wp_ajax_mrhe_attachment_delete_modal', 'mrhe_attachment_ajax_delete_modal');

function mrhe_attachment_delete_attachment() {
    // 执行安全验证检查
    zib_ajax_verify_nonce();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!$id || !current_user_can('delete_post', $id)) {
        zib_send_json_error('您没有删除此附件的权限！');
    }

    $result = wp_delete_attachment($id, true);

    if ($result) {
        zib_send_json_success(array('msg' => '删除成功！', 'reload' => true));
    } else {
        zib_send_json_error('删除失败！');
    }
}
add_action('wp_ajax_mrhe_attachment_delete_attachment', 'mrhe_attachment_delete_attachment');

function mrhe_attachment_delete_link($attachment_id = 0, $class = '', $con = '<i class="fa fa-trash-o fa-fw"></i>删除', $tag = 'a') {
    if (!$attachment_id || !current_user_can('delete_post', $attachment_id)) {
        return;
    }

    $url_var = array(
        'action' => 'mrhe_attachment_delete_modal',
        'id'     => $attachment_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'modal-mini',
        'height'        => 240,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    return zib_get_refresh_modal_link($args);
}

function mrhe_attachment_view_link($attachment_id = 0, $class = '', $con = '<i class="fa fa-eye fa-fw"></i>查看', $tag = 'a') {
    if (!$attachment_id || !current_user_can('read_post', $attachment_id)) {
        return;
    }

    $url_var = array(
        'action' => 'mrhe_attachment_view_modal',
        'id'     => $attachment_id,
    );

    $args = array(
        'tag'           => $tag,
        'class'         => $class,
        'data_class'    => 'full-sm',
        'height'        => 400,
        'mobile_bottom' => true,
        'text'          => $con,
        'query_arg'     => $url_var,
    );

    return zib_get_refresh_modal_link($args);
}

function mrhe_attachment_ajax_view_modal() {
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

    if (!$id) {
        zib_ajax_notice_modal('danger', '参数传入错误');
    }

    // 获取附件对象
    $attachment = get_post($id);
    $file_url = wp_get_attachment_url($id);
    $mime_type = get_post_mime_type($id);

    if (empty($attachment->ID) || 'attachment' !== $attachment->post_type || !$file_url) {
        zib_ajax_notice_modal('danger', '附件不存在或参数传入错误');
    }

    // 准备HTML输出
    $html = '<div class="mb10 touch"><div class="mr10 inflex em12">' . zib_get_svg('poster-color', null, 'em14 mr10') . '<b>查看附件</b></div><button class="close" data-dismiss="modal">' . zib_get_svg('close', null, 'ic-close') . '</button></div>';
    $html .= '<div class="mini-scrollbar scroll-y max-vh5">';
    
    // 根据文件类型显示不同内容
    if (strpos($mime_type, 'image/') === 0) {
        // 图片文件 - 显示图片
        $full_url = wp_get_attachment_image_src($id, 'full');
        $html .= '<div class="imgbox-container">';
        $html .= '<img class="fit-cover lazyloaded lazyloadafter" src="' . esc_url($full_url[0]) . '" data-src="' . esc_url($full_url[0]) . '" alt="' . esc_attr($attachment->post_title) . '" imgbox-index="0">';
        $html .= '</div>';
    } elseif (strpos($mime_type, 'video/') === 0 && _mrhe('attachment_preview_video')) {
        // 视频文件 - 使用主题内置的DPlayer播放器
        $poster = wp_get_attachment_image_src($id, 'medium');
        $poster_url = $poster ? $poster[0] : '';

        // 使用zib_get_dplayer函数构建播放器
        $dplayer_html = zib_get_dplayer($file_url, $poster_url, 0);
        $html .= $dplayer_html;
    } elseif (strpos($mime_type, 'audio/') === 0 && _mrhe('attachment_preview_audio')) {
        // 音频文件 - 使用原生 audio 控件轻量播放
        $html .= '<div class="mb20"><audio controls preload="none" style="width:100%">'
              . '<source src="' . esc_url($file_url) . '" type="' . esc_attr($mime_type) . '">' 
              . '您的浏览器不支持音频播放'
              . '</audio></div>';
    } else {
        // 其它类型：保留之前的排版，不显示URL，并提示无法预览
        $icon = mrhe_attachment_get_file_type_icon($mime_type);
        $html .= '<div class="text-center padding-20">';
        $html .= '  <div class="mb20" style="display:inline-block;font-size:54px;line-height:1">' . $icon . '</div>';
        $html .= '<div class="mb20"><b class="em12">' . esc_html($attachment->post_title) . '</b></div>';
        $html .= '  <div class="mb20 em12 muted-2-color">当前文件不支持预览，请下载后查看</div>';
        $html .= '</div>';
    }
    $html .= '</div>';

    // 文件信息栏：图标、文件名、大小、下载按钮（动态）
    $file_icon = mrhe_attachment_get_file_type_icon($mime_type);
    $file_size = mrhe_attachment_format_file_size($id);
    $html .= '<div class="modal-buts but-average">';
    $html .= '<div class="border-bottom padding-h10 flex jsb" style="width: 100%;margin: 0 20px;">';
    $html .= '  <div class="inflex ai-center">';
    $html .= '    <span class="mr10 file-type-icon" style="display:flex;align-items:center;justify-content:center;font-size: 35px;">' . $file_icon . '</span>';
    $html .= '    <div class="muted-2-color">';
    $html .= '      <div class="mb6">文件名：' . esc_html($attachment->post_title) . '</div>';
    $html .= '      <div class="em09">大小：' . esc_html($file_size) . '</div>';
    $html .= '    </div>';
    $html .= '  </div>';
    $html .= '  <div class="flex jsb xx text-right flex0 ml10 ab">';
    $html .= '    <a href="' . esc_url($file_url) . '" class="but c-blue" download><i class="fa fa-download"></i>下载文件</a>';
    $html .= '  </div>';
    $html .= '</div>';
    $html .= '</div>';

    echo $html;
    exit;
}
add_action('wp_ajax_mrhe_attachment_view_modal', 'mrhe_attachment_ajax_view_modal');
add_action('wp_ajax_nopriv_mrhe_attachment_view_modal', 'mrhe_attachment_ajax_view_modal');
