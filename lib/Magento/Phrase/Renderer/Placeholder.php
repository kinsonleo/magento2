<?php
/**
 * Placeholder Phrase renderer
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Phrase\Renderer;

class Placeholder implements \Magento\Phrase\RendererInterface
{
    /**
     * {@inheritdoc}
     *
     * @param string $text
     * @param array $arguments
     * @return string
     */
    public function render($text, array $arguments)
    {
        if ($arguments) {
            $placeholders = array();
            foreach (array_keys($arguments) as $key) {
                $placeholders[] = "%" . (is_int($key) ? strval($key + 1) : $key);
            }
            $text = str_replace($placeholders, $arguments, $text);
        }
        return $text;
    }
}
