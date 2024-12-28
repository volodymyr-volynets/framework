<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Object\Traits;

use Object_Enum_LocAttribute;

trait EnumTrait
{
    /**
     * @param self $enum
     */
    protected static function getLocAttributes(self $enum): array|false
    {
        $ref = new \ReflectionClassConstant(self::class, $enum->name);
        $attributes = $ref->getAttributes(Object_Enum_LocAttribute::class);
        if (count($attributes) === 0) {
            return false;
        } else {
            $object = $attributes[0]->newInstance();
            return [
                'loc' => $object->loc,
                'name' => $object->name,
                'description' => $object->description,
            ];
        }
    }

    /**
     * Options
     *
     * @param array $options
     * @return array
     */
    public static function options($options = [])
    {
        $result = [];
        foreach (self::cases() as $v) {
            $name = (new \String2($v->name))->spaceOnUpperCase()->toString();
            $temp = self::getLocAttributes($v);
            if ($temp === false) {
                $result[$v->value] = ['name' => $name];
            } else {
                $result[$v->value] = $temp;
                if (isset($temp['description'])) {
                    $result[$v->value]['loc_description'] = \String2::createStatic($temp['description'])->englishOnly(true)->toString();
                }
                if (!isset($result[$v->value]['name'])) {
                    $result[$v->value]['name'] = $name;
                }
            }
        }
        return $result;
    }
}
