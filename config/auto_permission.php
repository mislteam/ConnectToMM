<?php

return [
    'overrides' => [
        'esim.index' => 'joytel.esim.menu',
        'esim.create' => 'joytel.esim.create',
        'esim.store' => 'joytel.esim.create',
        'esim.edit' => 'joytel.esim.edit',
        'esim.update' => 'joytel.esim.edit',

        'physical.index' => 'joytel.physical.menu',
        'physical.edit' => 'joytel.physical.edit',
        'physical.update' => 'joytel.physical.edit',
        'physical.create' => 'joytel.physical.create',
        'physical.store' => 'joytel.physical.create',

        'region.index' => 'joytel.region.menu',
        'region.create' => 'joytel.region.create',
        'region.store' => 'joytel.region.create',
        'region.edit' => 'joytel.region.edit',
        'region.update' => 'joytel.region.edit',

        'roamEsimIndex' => 'roam.esim.menu',
        'roamEsimEdit' => 'roam.esim.edit',
        'roam.update' => 'roam.esim.edit',

        'roamphysical.Index' => 'roam.physical.menu',
        'roamPhysicalEdit' => 'roam.physical.edit',
        'roamphysical.update' => 'roam.physical.edit',

        'roam.updatePackageStatus' => 'roam.esim.edit',
        'roamphysical.updatePackageStatus' => 'roam.physical.edit',
        'pricelist.store' => 'roam.esim.edit',
        'physicalpricelist.store' => 'roam.physical.edit',

        'roamSkuIndex' => 'roam.esimSKU.menu',
        'roam-skus.toggle-status' => 'roam.esimSKU.edit',

        'roamphysical.SkuIndex' => 'roam.physicalSKU.menu',
        'roam-physicalskus.toggle-status' => 'roam.physicalSKU.edit',

        'roamApiIndex' => 'roam.api-credentials.menu',
        'roam-api.store' => 'roam.api-credentials.edit',

        'updateData' => 'roam.esim-update.menu',
        'roamSkuPackages' => 'roam.esim-update.create',
        'physical.updateData' => 'roam.physical-update.menu',
        'roamphysical.SkuPackages' => 'roam.physical-update.create',

        'show.admin' => 'admin.menu',
        'create.admin' => 'admin.create',
        'admin.store' => 'admin.create',
        'admin.update' => 'admin.edit',
        'admin.change-password' => 'admin.edit',
        'generalIndex' => 'general.menu',
        'generalEdit' => 'general.edit',
        'generalUpdate' => 'general.edit',

        'admin.payment.index' => 'payment.menu',
        'admin.payment.edit' => 'payment.edit',
        'admin.payment.update-status' => 'payment.edit',
        'payment.direct.store' => 'payment.edit',
        'payment.direct.update' => 'payment.edit',
        'payment.uab.update' => 'payment.edit',
        'payment.direct.delete' => 'payment.delete',

        'page.home.index' => 'page.menu',
        'page.about.index' => 'page.menu',
        'page.common.index' => 'page.menu',

        'page.section.edit' => 'page.edit',
        'page.section.update' => 'page.edit',

        'page.faq.index' => 'page.menu',
        'page.faq.create' => 'page.create',
        'page.faq.store' => 'page.create',
        'page.faq.edit' => 'page.edit',
        'page.faq.update' => 'page.edit',
        'page.faq.delete' => 'page.delete',

        'page.banner.index' => 'page.menu',
        'page.banner.edit' => 'page.edit',
        'page.banner.update' => 'page.edit',
        'page.refunds.index' => 'page.menu',
        'page.refunds.update' => 'page.edit',

        'footer.contact.index' => 'page.menu',
        'footer.contact.edit' => 'page.edit',
        'footer.contact.update' => 'page.edit',

        'footer.support.index' => 'page.menu',
        'footer.support.edit' => 'page.edit',
        'footer.support.update' => 'page.edit',

        'footer.important.index' => 'page.menu',
        'footer.important.edit' => 'page.edit',
        'footer.important.update' => 'page.edit',
    ]
];
