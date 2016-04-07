<?php

namespace SpyimmoBundle\Logger;


use Symfony\Component\Console\Output\OutputInterface;

class SpyimmoLogger
{
    protected $logger;

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function logInfo($message)
    {
        if ($this->logger) {
            $this->logger->text($message);
        }
    }

    public function logDebug($message)
    {
        if ($this->logger && $this->logger->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE) {
            $this->logger->text($message);
        }
    }

    public function logNote($message)
    {
        if ($this->logger) {
            $this->logger->note($message);
        }
    }

    public function logError($message)
    {
        if ($this->logger) {
            $this->logger->error($message);
        }
    }

    public function logSection($message)
    {
        if ($this->logger) {
            $this->logger->section($message);
        }
    }


}