<?php
declare(strict_types=1);

namespace Metasync\LogViewer\Block\Adminhtml\Log;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\View\Element\Template;

class View extends Template
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var File
     */
    private $fileDriver;

    /**
     * @param Template\Context $context
     * @param DirectoryList $directoryList
     * @param File $fileDriver
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        DirectoryList    $directoryList,
        File             $fileDriver,
        array            $data = []
    ) {
        parent::__construct($context, $data);
        $this->directoryList = $directoryList;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @throws FileSystemException
     *
     * @return array
     */
    public function getLogFiles(): array
    {
        $logDirectory = $this->directoryList->getPath(DirectoryList::LOG);
        $logFiles = [];
        $this->getAllLogFiles($logDirectory, $logFiles);

        return $logFiles;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getHumanName(string $path): string
    {
        $parentDir = basename(dirname($path));
        $basename = basename($path);
        $result = $parentDir . DIRECTORY_SEPARATOR . $basename;

        return str_replace('log/', '', $result);
    }

    /**
     * @return string
     */
    public function getAjaxUrl(): string
    {
        return $this->getUrl('logviewer/log/download');
    }

    /**
     * @param string $directory
     * @param array $logFiles
     *
     * @throws FileSystemException
     *
     * @return void
     */
    private function getAllLogFiles(string $directory, array &$logFiles): void
    {
        $files = $this->fileDriver->readDirectory($directory);

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if ($this->fileDriver->isDirectory($file)) {
                $this->getAllLogFiles($file, $logFiles);
            } elseif (pathinfo($file, PATHINFO_EXTENSION) === 'log') {
                $logFiles[] = $file;
            }
        }
    }
}
