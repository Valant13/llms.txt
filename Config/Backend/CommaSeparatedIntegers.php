<?php declare(strict_types=1);

namespace MageOS\LlmTxt\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use MageOS\LlmTxt\Service\CsvSerializer;

class CommaSeparatedIntegers extends Value
{
    public function __construct(
        private readonly CsvSerializer $csvSerializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @throws ValidatorException
     */
    public function beforeSave(): self
    {
        $value = (string) $this->getValue();
        if (empty($value)) {
            return parent::beforeSave();
        }

        $parts = $this->csvSerializer->unserialize($value);

        foreach ($parts as $part) {
            if (!ctype_digit($part) || (int) $part <= 0) {
                throw new ValidatorException(
                    __('"%1" is not a valid positive integer. Please enter a comma-separated list of positive integers.', $part)
                );
            }
        }

        $this->setValue($this->csvSerializer->serialize($parts));

        return parent::beforeSave();
    }
}
