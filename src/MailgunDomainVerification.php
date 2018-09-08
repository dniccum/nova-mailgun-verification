<?php

namespace Dniccum\MailgunDomainVerification;

use Laravel\Nova\ResourceTool;

class MailgunDomainVerification extends ResourceTool
{
    /**
     * Get the displayable name of the resource tool.
     *
     * @return string
     */
    public function name()
    {
        return 'Mailgun Domain Verification';
    }

    /**
     * If you would like to override the default attribute provide the column you would like to reference here.
     *
     * @param string $attribute
     * @return MailgunDomainVerification
     */
    public function resourceAttribute(string $attribute)
    {
        return $this->withMeta([
            'attribute' => $attribute
        ]);
    }

    /**
     * Get the component name for the resource tool.
     *
     * @return string
     */
    public function component()
    {
        return 'mailgun-domain-verification';
    }
}
