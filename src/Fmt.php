<?php
namespace xingwenge\canal_php;

use Com\Alibaba\Otter\Canal\Protocol\Column;
use Com\Alibaba\Otter\Canal\Protocol\Entry;
use Com\Alibaba\Otter\Canal\Protocol\EntryType;
use Com\Alibaba\Otter\Canal\Protocol\EventType;
use Com\Alibaba\Otter\Canal\Protocol\RowChange;
use Com\Alibaba\Otter\Canal\Protocol\RowData;

class Fmt
{
    public static function parseEntry($entry) {
        switch ($entry->getEntryType()) {
            case EntryType::TRANSACTIONBEGIN:
            case EntryType::TRANSACTIONEND:
                return;
                break;
        }

        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $evenType = $rowChange->getEventType();
        $header = $entry->getHeader();

        $table_name = $header->getTableName();
        $sql = $rowChange->getSql();

        $refreshIds = [];
        /** @var RowData $rowData */
        foreach ($rowChange->getRowDatas() as $rowData) {
            switch ($evenType) {
                case EventType::INSERT:
                    $columns = $rowData->getAfterColumns();
                    break;
                default:
                    $columns = $rowData->getBeforeColumns();
                    break;
            }
            if ($columns) {
                foreach ($columns as $column) {
                    $columnName = $column->getName();
                    if ($columnName == "id") {
                        $refreshIds[] = $column->getValue();
                    }
                }
                echo("表名: " . $table_name . ", sql语句: " . $sql . ", id: " . implode(',', $refreshIds)) . PHP_EOL;
            }
        }

    }

    /**
     * @param Entry $entry
     * @throws \Exception
     */
    public static function println($entry)
    {
        switch ($entry->getEntryType()) {
            case EntryType::TRANSACTIONBEGIN:
            case EntryType::TRANSACTIONEND:
                return;
                break;
        }

        $rowChange = new RowChange();
        $rowChange->mergeFromString($entry->getStoreValue());
        $evenType = $rowChange->getEventType();
        $header = $entry->getHeader();

        echo sprintf("================> binlog[%s : %d],name[%s,%s], eventType: %s", $header->getLogfileName(), $header->getLogfileOffset(), $header->getSchemaName(), $header->getTableName(), $header->getEventType()), PHP_EOL;
        echo $rowChange->getSql(), PHP_EOL;

        /** @var RowData $rowData */
        foreach ($rowChange->getRowDatas() as $rowData) {
            switch ($evenType) {
                case EventType::DELETE:
                    self::ptColumn($rowData->getBeforeColumns());
                    break;
                case EventType::INSERT:
                    self::ptColumn($rowData->getAfterColumns());
                    break;
                default:
                    echo '-------> before', PHP_EOL;
                    self::ptColumn($rowData->getBeforeColumns());
                    echo '-------> after', PHP_EOL;
                    self::ptColumn($rowData->getAfterColumns());
                    break;
            }
        }
    }

    private static function ptColumn($columns) {
        /** @var Column $column */
        foreach ($columns as $column) {
            echo sprintf("%s : %s  update= %s", $column->getName(), $column->getValue(), var_export($column->getUpdated(), true)), PHP_EOL;
        }
    }
}