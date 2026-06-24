<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Validator;

class EmailPublic extends Base
{
    /**
     * @var array
     */
    public $loc = [
        'NF.Validator.TheDomainIsPublic' => 'The domain {domain} is public!',
        'NF.Validator.InvalidEmailAddress' => 'Invalid email address!',
        'NF.Validator.EmailIsPublicProvider' => 'Email is public email provider!',
        'NF.Validator.DoYouMeanThisDomain' => 'Do you mean {domain}?',
    ];

    /**
     * @see Base::validate()
     */
    public function validate($value, $options = [])
    {
        $result = $this->result;
        $result['placeholder'] = 'example@domain.com';
        $data = filter_var($value, FILTER_VALIDATE_EMAIL);
        if ($data !== false) {
            $result['data'] = strtolower($data . '');
            $domain = explode('@', $result['data'])[1] ?? '';
            $lines = file(__DIR__ . DIRECTORY_SEPARATOR . 'Data' . DIRECTORY_SEPARATOR . 'public_email_domains.txt', FILE_IGNORE_NEW_LINES);
            if (in_array($domain, $lines)) {
                if (!empty($options['mandatory_not_public'])) {
                    $result['error'][] = loc('NF.Validator.TheDomainIsPublic', 'The domain {domain} is public!', [
                        'domain' => $domain,
                    ]);
                    return $result;
                }
                $result['success'] = true;
                $result['warning'][] = loc('NF.Validator.EmailIsPublicProvider', 'Email is public email provider!');
                return $result;
            } else {
                $shortest = -1;
                $closest = '';
                foreach ($lines as $v) {
                    $lev = levenshtein($domain, $v);
                    if ($lev <= $shortest || ($shortest == -1 && $lev <= 2)) {
                        $closest = $v;
                        $shortest = $lev;
                    }
                }
            }
            // if we found similar domain
            if ($shortest != -1) {
                $result['success'] = true;
                $result['warning'][] = loc('NF.Validator.DoYouMeanThisDomain', 'Do you mean {domain}?', [
                    'domain' => $closest,
                ]);
                return $result;
            }
        } else {
            $result['error'][] = loc('NF.Validator.InvalidEmailAddress', 'Invalid email address!');
        }
        return $result;
    }
}
