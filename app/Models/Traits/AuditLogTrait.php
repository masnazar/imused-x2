<?php

namespace App\Models\Traits;

use App\Services\AuditLogService;

trait AuditLogTrait
{
    protected $auditLogService;

    public function initializeAuditLogTrait()
    {
        $this->auditLogService = new AuditLogService();
        
        $this->afterInsert[] = 'auditInsert';
        $this->afterUpdate[] = 'auditUpdate';
        $this->afterDelete[] = 'auditDelete';
        
        if (method_exists($this, 'deleted') && property_exists($this, 'useSoftDeletes')) {
            $this->afterRestore[] = 'auditRestore';
        }
    }

    protected function auditInsert(array $data)
    {
        $this->auditLogService->log(
            $this->getCurrentUserId(),
            static::class,
            $data['id'] ?? $this->getInsertID(),
            'create',
            null,
            $data
        );
    }

    protected function auditUpdate(array $data)
    {
        $this->auditLogService->log(
            $this->getCurrentUserId(),
            static::class,
            $this->getPrimaryKeyValue(),
            'update',
            $this->getOriginalData(),
            $data
        );
    }

    protected function auditDelete()
    {
        $this->auditLogService->log(
            $this->getCurrentUserId(),
            static::class,
            $this->getPrimaryKeyValue(),
            'delete',
            $this->getOriginalData(),
            null
        );
    }

    protected function auditRestore()
    {
        $this->auditLogService->log(
            $this->getCurrentUserId(),
            static::class,
            $this->getPrimaryKeyValue(),
            'restore',
            null,
            $this->toArray()
        );
    }

    private function getPrimaryKeyValue()
    {
        return $this->{$this->primaryKey};
    }

    private function getOriginalData()
    {
        return $this->original;
    }

    private function getCurrentUserId()
    {
        // Implement your user authentication logic here
        return auth()->user()->id ?? null;
    }
}