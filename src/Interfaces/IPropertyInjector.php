<?php

namespace Amorphine\DataTransferObject\Interfaces;

interface IPropertyInjector
{
    /**
     * Inject source data into DTO
     *
     * @param  IDataTransferObject  $target
     * @param $source
     */
    public function inject(IDataTransferObject $target, $source);

    /**
     * Create instance of property injector
     *
     * @return IPropertyInjector
     */
    public static function getInstance(): self;
}
