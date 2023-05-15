<?php
/**
 *   Author:Brown
 *   Email: 455764041@qq.com
 *   Time: 2023-05-13 09:01
 */

namespace Dtm;

interface BranchIdGeneratorInterface
{
    public function generateSubBranchId(): string;

    public function getCurrentSubBranchId(int $subBranchId): string;
}