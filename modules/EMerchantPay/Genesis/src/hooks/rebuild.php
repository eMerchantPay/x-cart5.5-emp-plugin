<?php

use XLite\Core\Database;

const EMP_DIRECT = 'EmerchantpayDirect';

return new \XLite\Rebuild\Hook(
    function () {
        $repo = Database::getRepo('XLite\Model\Payment\Method');
        $isEmpDirectInstalled = $repo->findOneBy([
            'service_name' => EMP_DIRECT,
        ]);

        if ($isEmpDirectInstalled) {
            $isEmpDirectInstalled->setEnabled(0)->setAdded(0);
        }

        Database::getEM()->flush();
    }
);
