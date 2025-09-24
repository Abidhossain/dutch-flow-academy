<?php

namespace FluentSupportPro\App\Services\Workflow;

use FluentSupport\App\Services\Helper;
use FluentSupportPro\App\Services\TicketBookmarkService;

class ConditionChecker
{
	public function check($workFlow, $source, $customer)
	{
		$conditionGroups = $workFlow->settings['conditions'];

		$isTriggeredByTicket = strpos($workFlow->trigger_key, 'ticket');

		foreach ($conditionGroups as $conditionGroup) {
			if (!$this->matchTicketConditionGroup($conditionGroup, $source, $customer, $isTriggeredByTicket)) {
				return false;
			}
		}

		return true;
	}

	private function matchTicketConditionGroup($conditionGroup, $source, $customer, $isTriggeredByTicket)
	{
		foreach ($conditionGroup as $condition) {
			$dataKey = $condition['data_key'];
			$operator = $condition['data_operator'];
			$accessor = explode('.', $dataKey);

			if (count($accessor) != 2 || !$operator) {
				return true;
			}

			$dataProvider = $accessor[0];
			$condition['property'] = $accessor[1];

			switch ($dataProvider) {
				case 'customer':
					$target = $customer;
					break;
				case 'ticket':
					$target = $isTriggeredByTicket ? $source : $source->ticket;
					break;
                case 'custom_fields':
                    $target = $isTriggeredByTicket ? json_decode(json_encode($source->customData()), FALSE) : json_decode(json_encode($source->ticket->customData()), FALSE);
                    break;
				default:
					$target = $source;
			}
            $target = is_array($target) ? (object) $target :  $target;

			$match = $this->match($condition, $target);

			if ($match) {
				return true;
			}
		}

		return false;
	}

	private function match($condition, $target)
	{
        $conditionGroup = explode('.', $condition['data_key'])[0];
		switch ($condition['data_operator']) {
			case 'contains':
                if(!$this->isPropertyExist($target, $condition['property'])){
                    return false;
                }
                return mb_stripos($target->{$condition['property']}, $condition['data_value']) !== false;
			case 'not_contains':
                if (!$this->isPropertyExist($target, $condition['property'])) {
                    return false;
                }
				return mb_stripos($target->{$condition['property']}, $condition['data_value']) === false;
			case 'yes':
				return $target->attachments->count();
			case 'no':
				return !$target->attachments->count();
			case 'range':
				if ($condition['property'] === 'added_time_range') {
					$target = strtotime(date('H:i:s', strtotime($target->created_at)));
				} else {
					$target = strtotime($target->created_at);
				}
				return $target >= strtotime($condition['value_1']) && $target <= strtotime($condition['value_2']);
			case '>':
				return strtotime($target->created_at) > strtotime($condition['data_value']);
			case '<':
				return strtotime($target->created_at) < strtotime($condition['data_value']);
			case 'equal':
				if(!$this->isPropertyExist($target, $condition['property'])){
                    return false;
                }
                return $target->{$condition['property']} == $condition['data_value'];
			case 'not_equal':
                if (!$this->isPropertyExist($target, $condition['property'])) {
                    return false;
                }
				return $target->{$condition['property']} != $condition['data_value'];
            case 'includes_in':
                if($conditionGroup == 'custom_fields' && isset($target->{$condition['property']})){
                    foreach ($condition['data_value'] as $value){
                        if(!in_array($value, $target->{$condition['property']})){
                            return false;
                        }
                    }
                    return true;
                }
                if ($conditionGroup == 'fluent_crm'){
                    $customer = $target->customer->id;
                    if($target->conversation_type == 'response'){
                        $customer = $target->person_id;
                    }
                    return $this->matchCRMTagsOrLists($condition['property'], $condition['data_value'], $customer);
                }
                if( $condition['property'] == 'tag_id'){
                    return $this->matchBookmarks($condition, $target);
                }
            case 'not_includes_in':
                if($conditionGroup == 'custom_fields' && isset($target->{$condition['property']})){
                    foreach ($condition['data_value'] as $value){
                        if(!in_array($value, $target->{$condition['property']})){
                            return true;
                        }
                    }
                    return false;
                }
                if ($conditionGroup == 'fluent_crm'){
                    $customer = $target->customer->id;
                    if($target->conversation_type == 'response'){
                        $customer = $target->person_id;
                    }
                    return !$this->matchCRMTagsOrLists($condition['property'], $condition['data_value'], $customer);
                }

                if( $condition['property'] == 'tag_id'){
                    return !$this->matchBookmarks($condition, $target);
                }
		}

		return false;
	}

    private function matchBookmarks($condition, $target){
        $ticketId = $target->ticket->id;
        $values = $condition['data_value'];//condition values from workflow
        $bookmarks = (new TicketBookmarkService())->getExistingBookmarks($ticketId)->toArray();//Bookmarks exists for ticket by id

        $match = false;
        switch ($condition['data_operator']) {
            case 'includes_in':
                $match = count(array_intersect($values, array_column($bookmarks, 'tag_id'))) == count($values);
                break;
            case 'not_includes_in':
                $match = count(array_intersect($values, array_column($bookmarks, 'tag_id'))) != count($values);
                break;
        }

        return $match;
    }

    private function matchCRMTagsOrLists($type, $condition, $customer)
    {
        $customer = Helper::getCustomerByID($customer);

        if(!$customer){
            return false;
        }

        if(function_exists('\FluentCrmApi')) {
            $crmUser= \FluentCrmApi('contacts')->getContact($customer->email);
        }

        if(!$crmUser){
            return false;
        }

        if($type=='lists') {
            if($crmUser && $crmUser->hasAnyListId($condition)) {
                return true;
            } else{
                return false;
            }
        } else {
            if($crmUser && $crmUser->hasAnyTagId($condition)) {
                return true;
            } else{
                return false;
            }
        }
    }

    private function isPropertyExist($object, $target){
        if(is_array($object)){
            return array_key_exists($target, $object);
        }else if($object instanceof \stdClass){
            return property_exists($object, $target);
        }else if (is_string($object)){
            return false;
        }else if($object instanceof \FluentSupport\App\Models\Model){
            return isset($object->{$target});
        }
        return false;
    }
}
