<?php
/*
 * @Author        : Qinver
 * @Url           : zibll.com
 * @Date          : 2025-01-15 10:00:00
 * @LastEditTime : 2025-01-15 10:00:00
 * @Email         : 770349780@qq.com
 * @Project       : Zibll子比主题
 * @Description   : mrhe主题授权系统|域名处理工具类
 * @Read me       : 感谢您使用子比主题，主题源码有详细的注释，支持二次开发。
 * @Remind        : 使用盗版主题会存在各种未知风险。支持正版，从我做起！
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 域名处理工具类
 * 统一管理所有域名相关操作
 */
class MrheDomainHelper
{
    /**
     * 反序列化域名数据
     * @param mixed $serialized_domains 序列化的域名数据
     * @return array 域名数组
     */
    public static function unserializeDomains($serialized_domains)
    {
        $domains = maybe_unserialize($serialized_domains);
        return is_array($domains) ? $domains : array();
    }

    /**
     * 序列化域名数据
     * @param array $domains 域名数组
     * @return string 序列化后的字符串
     */
    public static function serializeDomains($domains)
    {
        return is_array($domains) ? serialize($domains) : serialize(array());
    }

    /**
     * 清理和标准化域名
     * @param string $domain 原始域名
     * @return string 清理后的域名
     */
    public static function cleanDomain($domain)
    {
        // 移除协议
        $domain = preg_replace('#^https?://#i', '', $domain);

        // 移除路径
        $domain = preg_replace('#/.*$#', '', $domain);

        // 转小写
        $domain = strtolower(trim($domain));

        // 移除端口号
        $domain = preg_replace('#:\d+$#', '', $domain);

        return $domain;
    }

    /**
     * 验证域名格式
     * @param string $domain 域名
     * @return bool 是否有效
     */
    public static function validateDomain($domain)
    {
        // 清理域名
        $domain = self::cleanDomain($domain);

        // 检查是否为空
        if (empty($domain)) {
            return false;
        }

        // 检查域名格式
        $pattern = '/^([a-z0-9]([a-z0-9\-]{0,61}[a-z0-9])?\.)+[a-z]{2,}$/i';
        return preg_match($pattern, $domain) === 1;
    }

    /**
     * 批量验证域名
     * @param array $domains 域名数组
     * @return array 验证结果 ['valid' => [], 'invalid' => []]
     */
    public static function validateDomains($domains)
    {
        $result = array(
            'valid' => array(),
            'invalid' => array()
        );

        foreach ($domains as $domain) {
            if (self::validateDomain($domain)) {
                $result['valid'][] = self::cleanDomain($domain);
            } else {
                $result['invalid'][] = $domain;
            }
        }

        return $result;
    }

    /**
     * 检查域名是否存在于列表中
     * @param string $domain 要检查的域名
     * @param array $domain_list 域名列表
     * @return bool 是否存在
     */
    public static function domainExistsInList($domain, $domain_list)
    {
        $clean_domain = self::cleanDomain($domain);

        foreach ($domain_list as $item) {
            $list_domain = is_array($item) ? ($item['domain'] ?? '') : $item;
            if (self::cleanDomain($list_domain) === $clean_domain) {
                return true;
            }
        }

        return false;
    }

    /**
     * 添加域名到列表
     * @param string $domain 要添加的域名
     * @param array $domain_list 现有域名列表
     * @return array 更新后的域名列表
     */
    public static function addDomainToList($domain, $domain_list)
    {
        $clean_domain = self::cleanDomain($domain);

        // 检查是否已存在
        if (self::domainExistsInList($clean_domain, $domain_list)) {
            return $domain_list;
        }

        // 添加域名
        $domain_list[] = array(
            'domain' => $clean_domain,
            'added_at' => current_time('mysql')
        );

        return $domain_list;
    }

    /**
     * 从列表中移除域名
     * @param string $domain 要移除的域名
     * @param array $domain_list 现有域名列表
     * @return array 更新后的域名列表
     */
    public static function removeDomainFromList($domain, $domain_list)
    {
        $clean_domain = self::cleanDomain($domain);

        return array_filter($domain_list, function($item) use ($clean_domain) {
            $list_domain = is_array($item) ? ($item['domain'] ?? '') : $item;
            return self::cleanDomain($list_domain) !== $clean_domain;
        });
    }

    /**
     * 计算已使用的域名数量
     * @param array $domain_list 域名列表
     * @return int 域名数量
     */
    public static function countUsedDomains($domain_list)
    {
        if (!is_array($domain_list)) {
            return 0;
        }

        // 过滤掉空值
        $valid_domains = array_filter($domain_list, function($item) {
            $domain = is_array($item) ? ($item['domain'] ?? '') : $item;
            return !empty($domain);
        });

        return count($valid_domains);
    }

    /**
     * 获取显示用的域名列表(过滤www子域名)
     * @param array $domain_list 域名列表
     * @return array 显示用域名列表
     */
    public static function getDisplayDomains($domain_list)
    {
        if (!is_array($domain_list)) {
            return array();
        }

        $display_domains = array();

        foreach ($domain_list as $item) {
            $domain = is_array($item) ? ($item['domain'] ?? '') : $item;

            // 跳过空域名
            if (empty($domain)) {
                continue;
            }

            // 跳过www子域名(如果主域名也在列表中)
            if (strpos($domain, 'www.') === 0) {
                $main_domain = substr($domain, 4);
                if (self::domainExistsInList($main_domain, $domain_list)) {
                    continue;
                }
            }

            $display_domains[] = $domain;
        }

        return $display_domains;
    }
}
