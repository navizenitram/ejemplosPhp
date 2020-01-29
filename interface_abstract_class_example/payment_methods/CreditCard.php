<?php
namespace Domain;

interface CreditCard
{
    public function add(array $customerData): array;
}