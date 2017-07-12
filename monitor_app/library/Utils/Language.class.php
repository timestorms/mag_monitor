<?php
class Language{
	/**
	 * 订单的JSON格式不能解析
	 * @var string
	 */
	const ERROR_MESSAGE_ORDER_FORMAT = 'The order JSON data format is not correct';
	/**
	 * 指定的商品不存在
	 * @var string
	 */
	const ERROR_MESSAGE_GOODS_NOT_EXITS = 'The goods do not exist';
	/**
	 * 商品库存不足
	 * @var string
	 */
	const ERROR_GOODS_STOCK_NUMBER = 'Inventory shortage';
}
?>