<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * @method \Magento\Sales\Model\Resource\Order\Invoice\Item _getResource()
 * @method \Magento\Sales\Model\Resource\Order\Invoice\Item getResource()
 * @method float getBaseWeeeTaxRowDisposition()
 * @method \Magento\Sales\Model\Order\Invoice\Item setBaseWeeeTaxRowDisposition(float $value)
 * @method float getWeeeTaxAppliedRowAmount()
 * @method \Magento\Sales\Model\Order\Invoice\Item setWeeeTaxAppliedRowAmount(float $value)
 * @method float getBaseWeeeTaxAppliedAmount()
 * @method \Magento\Sales\Model\Order\Invoice\Item setBaseWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxRowDisposition()
 * @method \Magento\Sales\Model\Order\Invoice\Item setWeeeTaxRowDisposition(float $value)
 * @method float getBaseWeeeTaxDisposition()
 * @method \Magento\Sales\Model\Order\Invoice\Item setBaseWeeeTaxDisposition(float $value)
 * @method float getWeeeTaxAppliedAmount()
 * @method \Magento\Sales\Model\Order\Invoice\Item setWeeeTaxAppliedAmount(float $value)
 * @method float getWeeeTaxDisposition()
 * @method \Magento\Sales\Model\Order\Invoice\Item setWeeeTaxDisposition(float $value)
 * @method float getBaseWeeeTaxAppliedRowAmnt()
 * @method \Magento\Sales\Model\Order\Invoice\Item setBaseWeeeTaxAppliedRowAmnt(float $value)
 * @method string getWeeeTaxApplied()
 * @method \Magento\Sales\Model\Order\Invoice\Item setWeeeTaxApplied(string $value)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Item extends AbstractModel implements InvoiceItemInterface
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'sales_invoice_item';

    /**
     * @var string
     */
    protected $_eventObject = 'invoice_item';

    /**
     * @var \Magento\Sales\Model\Order\Item|null
     */
    protected $_orderItem = null;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItemFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Sales\Model\Order\ItemFactory $orderItemFactory
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Sales\Model\Order\ItemFactory $orderItemFactory,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\Resource\Order\Invoice\Item');
    }

    /**
     * Declare invoice instance
     *
     * @param \Magento\Sales\Api\Data\InvoiceInterface $invoice
     * @return $this
     */
    public function setInvoice(\Magento\Sales\Api\Data\InvoiceInterface $invoice)
    {
        return $this->setData(self::INVOICE, $invoice);
    }

    /**
     * Retrieve invoice instance
     *
     * @codeCoverageIgnore
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->getData(self::INVOICE);
    }

    /**
     * Declare order item instance
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @return $this
     */
    public function setOrderItem(\Magento\Sales\Model\Order\Item $item)
    {
        $this->_orderItem = $item;
        $this->setOrderItemId($item->getId());
        return $this;
    }

    /**
     * Retrieve order item instance
     *
     * @return \Magento\Sales\Model\Order\Item
     */
    public function getOrderItem()
    {
        if ($this->_orderItem === null) {
            if ($this->getInvoice()) {
                $this->_orderItem = $this->getInvoice()->getOrder()->getItemById($this->getOrderItemId());
            } else {
                $this->_orderItem = $this->_orderItemFactory->create()->load($this->getOrderItemId());
            }
        }
        return $this->_orderItem;
    }

    /**
     * Declare qty
     *
     * @codeCoverageIgnore
     *
     * @param float $qty
     * @return $this
     */
    public function setQty($qty)
    {
        return $this->setData(self::QTY, $qty);
    }

    /**
     * Applying qty to order item
     *
     * @return $this
     */
    public function register()
    {
        $orderItem = $this->getOrderItem();
        $orderItem->setQtyInvoiced($orderItem->getQtyInvoiced() + $this->getQty());

        $orderItem->setTaxInvoiced($orderItem->getTaxInvoiced() + $this->getTaxAmount());
        $orderItem->setBaseTaxInvoiced($orderItem->getBaseTaxInvoiced() + $this->getBaseTaxAmount());
        $orderItem->setHiddenTaxInvoiced($orderItem->getHiddenTaxInvoiced() + $this->getHiddenTaxAmount());
        $orderItem->setBaseHiddenTaxInvoiced($orderItem->getBaseHiddenTaxInvoiced() + $this->getBaseHiddenTaxAmount());

        $orderItem->setDiscountInvoiced($orderItem->getDiscountInvoiced() + $this->getDiscountAmount());
        $orderItem->setBaseDiscountInvoiced($orderItem->getBaseDiscountInvoiced() + $this->getBaseDiscountAmount());

        $orderItem->setRowInvoiced($orderItem->getRowInvoiced() + $this->getRowTotal());
        $orderItem->setBaseRowInvoiced($orderItem->getBaseRowInvoiced() + $this->getBaseRowTotal());
        return $this;
    }

    /**
     * Cancelling invoice item
     *
     * @return $this
     */
    public function cancel()
    {
        $orderItem = $this->getOrderItem();
        $orderItem->setQtyInvoiced($orderItem->getQtyInvoiced() - $this->getQty());

        $orderItem->setTaxInvoiced($orderItem->getTaxInvoiced() - $this->getTaxAmount());
        $orderItem->setBaseTaxInvoiced($orderItem->getBaseTaxInvoiced() - $this->getBaseTaxAmount());
        $orderItem->setHiddenTaxInvoiced($orderItem->getHiddenTaxInvoiced() - $this->getHiddenTaxAmount());
        $orderItem->setBaseHiddenTaxInvoiced($orderItem->getBaseHiddenTaxInvoiced() - $this->getBaseHiddenTaxAmount());

        $orderItem->setDiscountInvoiced($orderItem->getDiscountInvoiced() - $this->getDiscountAmount());
        $orderItem->setBaseDiscountInvoiced($orderItem->getBaseDiscountInvoiced() - $this->getBaseDiscountAmount());

        $orderItem->setRowInvoiced($orderItem->getRowInvoiced() - $this->getRowTotal());
        $orderItem->setBaseRowInvoiced($orderItem->getBaseRowInvoiced() - $this->getBaseRowTotal());
        return $this;
    }

    /**
     * Invoice item row total calculation
     *
     * @return $this
     */
    public function calcRowTotal()
    {
        $invoice = $this->getInvoice();
        $orderItem = $this->getOrderItem();
        $orderItemQty = $orderItem->getQtyOrdered();

        $rowTotal = $orderItem->getRowTotal() - $orderItem->getRowInvoiced();
        $baseRowTotal = $orderItem->getBaseRowTotal() - $orderItem->getBaseRowInvoiced();
        $rowTotalInclTax = $orderItem->getRowTotalInclTax();
        $baseRowTotalInclTax = $orderItem->getBaseRowTotalInclTax();

        if (!$this->isLast()) {
            $availableQty = $orderItemQty - $orderItem->getQtyInvoiced();
            $rowTotal = $invoice->roundPrice($rowTotal / $availableQty * $this->getQty());
            $baseRowTotal = $invoice->roundPrice($baseRowTotal / $availableQty * $this->getQty(), 'base');
        }

        $this->setRowTotal($rowTotal);
        $this->setBaseRowTotal($baseRowTotal);

        if ($rowTotalInclTax && $baseRowTotalInclTax) {
            $this->setRowTotalInclTax(
                $invoice->roundPrice($rowTotalInclTax / $orderItemQty * $this->getQty(), 'including')
            );
            $this->setBaseRowTotalInclTax(
                $invoice->roundPrice($baseRowTotalInclTax / $orderItemQty * $this->getQty(), 'including_base')
            );
        }
        return $this;
    }

    /**
     * Checking if the item is last
     *
     * @return bool
     */
    public function isLast()
    {
        if ((string)(double)$this->getQty() == (string)(double)$this->getOrderItem()->getQtyToInvoice()) {
            return true;
        }
        return false;
    }

    //@codeCoverageIgnoreStart
    /**
     * Returns additional_data
     *
     * @return string
     */
    public function getAdditionalData()
    {
        return $this->getData(InvoiceItemInterface::ADDITIONAL_DATA);
    }

    /**
     * Returns base_cost
     *
     * @return float
     */
    public function getBaseCost()
    {
        return $this->getData(InvoiceItemInterface::BASE_COST);
    }

    /**
     * Returns base_discount_amount
     *
     * @return float
     */
    public function getBaseDiscountAmount()
    {
        return $this->getData(InvoiceItemInterface::BASE_DISCOUNT_AMOUNT);
    }

    /**
     * Returns base_hidden_tax_amount
     *
     * @return float
     */
    public function getBaseHiddenTaxAmount()
    {
        return $this->getData(InvoiceItemInterface::BASE_HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns base_price
     *
     * @return float
     */
    public function getBasePrice()
    {
        return $this->getData(InvoiceItemInterface::BASE_PRICE);
    }

    /**
     * Returns base_price_incl_tax
     *
     * @return float
     */
    public function getBasePriceInclTax()
    {
        return $this->getData(InvoiceItemInterface::BASE_PRICE_INCL_TAX);
    }

    /**
     * Returns base_row_total
     *
     * @return float
     */
    public function getBaseRowTotal()
    {
        return $this->getData(InvoiceItemInterface::BASE_ROW_TOTAL);
    }

    /**
     * Returns base_row_total_incl_tax
     *
     * @return float
     */
    public function getBaseRowTotalInclTax()
    {
        return $this->getData(InvoiceItemInterface::BASE_ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns base_tax_amount
     *
     * @return float
     */
    public function getBaseTaxAmount()
    {
        return $this->getData(InvoiceItemInterface::BASE_TAX_AMOUNT);
    }

    /**
     * Returns description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(InvoiceItemInterface::DESCRIPTION);
    }

    /**
     * Returns discount_amount
     *
     * @return float
     */
    public function getDiscountAmount()
    {
        return $this->getData(InvoiceItemInterface::DISCOUNT_AMOUNT);
    }

    /**
     * Returns hidden_tax_amount
     *
     * @return float
     */
    public function getHiddenTaxAmount()
    {
        return $this->getData(InvoiceItemInterface::HIDDEN_TAX_AMOUNT);
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(InvoiceItemInterface::NAME);
    }

    /**
     * Returns order_item_id
     *
     * @return int
     */
    public function getOrderItemId()
    {
        return $this->getData(InvoiceItemInterface::ORDER_ITEM_ID);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(InvoiceItemInterface::PARENT_ID);
    }

    /**
     * Returns price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->getData(InvoiceItemInterface::PRICE);
    }

    /**
     * Returns price_incl_tax
     *
     * @return float
     */
    public function getPriceInclTax()
    {
        return $this->getData(InvoiceItemInterface::PRICE_INCL_TAX);
    }

    /**
     * Returns product_id
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(InvoiceItemInterface::PRODUCT_ID);
    }

    /**
     * Returns qty
     *
     * @return float
     */
    public function getQty()
    {
        return $this->getData(InvoiceItemInterface::QTY);
    }

    /**
     * Returns row_total
     *
     * @return float
     */
    public function getRowTotal()
    {
        return $this->getData(InvoiceItemInterface::ROW_TOTAL);
    }

    /**
     * Returns row_total_incl_tax
     *
     * @return float
     */
    public function getRowTotalInclTax()
    {
        return $this->getData(InvoiceItemInterface::ROW_TOTAL_INCL_TAX);
    }

    /**
     * Returns sku
     *
     * @return string
     */
    public function getSku()
    {
        return $this->getData(InvoiceItemInterface::SKU);
    }

    /**
     * Returns tax_amount
     *
     * @return float
     */
    public function getTaxAmount()
    {
        return $this->getData(InvoiceItemInterface::TAX_AMOUNT);
    }

    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(InvoiceItemInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePrice($price)
    {
        return $this->setData(InvoiceItemInterface::BASE_PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function setTaxAmount($amount)
    {
        return $this->setData(InvoiceItemInterface::TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRowTotal($amount)
    {
        return $this->setData(InvoiceItemInterface::BASE_ROW_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setDiscountAmount($amount)
    {
        return $this->setData(InvoiceItemInterface::DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setRowTotal($amount)
    {
        return $this->setData(InvoiceItemInterface::ROW_TOTAL, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseDiscountAmount($amount)
    {
        return $this->setData(InvoiceItemInterface::BASE_DISCOUNT_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setPriceInclTax($amount)
    {
        return $this->setData(InvoiceItemInterface::PRICE_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseTaxAmount($amount)
    {
        return $this->setData(InvoiceItemInterface::BASE_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBasePriceInclTax($amount)
    {
        return $this->setData(InvoiceItemInterface::BASE_PRICE_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseCost($baseCost)
    {
        return $this->setData(InvoiceItemInterface::BASE_COST, $baseCost);
    }

    /**
     * {@inheritdoc}
     */
    public function setPrice($price)
    {
        return $this->setData(InvoiceItemInterface::PRICE, $price);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseRowTotalInclTax($amount)
    {
        return $this->setData(InvoiceItemInterface::BASE_ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setRowTotalInclTax($amount)
    {
        return $this->setData(InvoiceItemInterface::ROW_TOTAL_INCL_TAX, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($id)
    {
        return $this->setData(InvoiceItemInterface::PRODUCT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderItemId($id)
    {
        return $this->setData(InvoiceItemInterface::ORDER_ITEM_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setAdditionalData($additionalData)
    {
        return $this->setData(InvoiceItemInterface::ADDITIONAL_DATA, $additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        return $this->setData(InvoiceItemInterface::DESCRIPTION, $description);
    }

    /**
     * {@inheritdoc}
     */
    public function setSku($sku)
    {
        return $this->setData(InvoiceItemInterface::SKU, $sku);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        return $this->setData(InvoiceItemInterface::NAME, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function setHiddenTaxAmount($amount)
    {
        return $this->setData(InvoiceItemInterface::HIDDEN_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseHiddenTaxAmount($amount)
    {
        return $this->setData(InvoiceItemInterface::BASE_HIDDEN_TAX_AMOUNT, $amount);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\InvoiceItemExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\InvoiceItemExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\Sales\Api\Data\InvoiceItemExtensionInterface $extensionAttributes)
    {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
