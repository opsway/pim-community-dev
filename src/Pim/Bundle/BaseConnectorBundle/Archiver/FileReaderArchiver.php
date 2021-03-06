<?php

namespace Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Akeneo\Bundle\BatchBundle\Step\ItemStep;
use Gaufrette\Filesystem;
use Pim\Bundle\BaseConnectorBundle\Reader\File\FileReader;

/**
 * Archive job execution files into conventional directories
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiver extends AbstractFilesystemArchiver
{
    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Archive files used by job execution (input / output)
     *
     * @param JobExecution $jobExecution
     */
    public function archive(JobExecution $jobExecution)
    {
        foreach ($jobExecution->getJobInstance()->getJob()->getSteps() as $step) {
            if (!$step instanceof ItemStep) {
                continue;
            }
            $reader = $step->getReader();

            if ($this->isReaderUsable($reader)) {
                $key = strtr(
                    $this->getRelativeArchivePath($jobExecution),
                    [
                        '%filename%' => basename($reader->getFilePath()),
                    ]
                );
                $this->filesystem->write($key, file_get_contents($reader->getFilePath()), true);
            }
        }
    }

    /**
     * Verify if the reader is usable or not
     *
     * @param ItemReaderInterface $reader
     *
     * @return bool
     */
    protected function isReaderUsable(ItemReaderInterface $reader)
    {
        return $reader instanceof FileReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'input';
    }

    /**
     * Check if the job execution is supported
     *
     * @param JobExecution $jobExecution
     *
     * @return bool
     */
    public function supports(JobExecution $jobExecution)
    {
        foreach ($jobExecution->getJobInstance()->getJob()->getSteps() as $step) {
            if ($step instanceof ItemStep && $this->isReaderUsable($step->getReader())) {
                return true;
            }
        }

        return false;
    }
}
