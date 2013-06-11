<?php
/**
Copyright 2011-2013 Nick Korbel

This file is part of phpScheduleIt.

phpScheduleIt is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

phpScheduleIt is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with phpScheduleIt.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ROOT_DIR . 'Pages/SecurePage.php');
require_once(ROOT_DIR . 'Presenters/SchedulePresenter.php');

interface ISchedulePage extends IActionPage
{
	/**
	 * Bind schedules to the page
	 *
	 * @param array|Schedule[] $schedules
	 */
	public function SetSchedules($schedules);

	/**
	 * Bind resources to the page
	 *
	 * @param arrayResourceDto[] $resources
	 */
	public function SetResources($resources);

	/**
	 * Bind layout to the page for daily time slot layouts
	 *
	 * @param IDailyLayout $dailyLayout
	 */
	public function SetDailyLayout($dailyLayout);

	/**
	 * Returns the currently selected scheduleId
	 * @return int
	 */
	public function GetScheduleId();

	/**
	 * @param int $scheduleId
	 */
	public function SetScheduleId($scheduleId);

	/**
	 * @param string $scheduleName
	 */
	public function SetScheduleName($scheduleName);

	/**
	 * @param int $firstWeekday
	 */
	public function SetFirstWeekday($firstWeekday);

	/**
	 * Sets the dates to be displayed for the schedule, adjusted for timezone if necessary
	 *
	 * @param DateRange $dates
	 */
	public function SetDisplayDates($dates);

	/**
	 * @param Date $previousDate
	 * @param Date $nextDate
	 */
	public function SetPreviousNextDates($previousDate, $nextDate);

	/**
	 * @return string
	 */
	public function GetSelectedDate();

	/**
	 * @abstract
	 */
	public function ShowInaccessibleResources();

	/**
	 * @abstract
	 * @param bool $showShowFullWeekToggle
	 */
	public function ShowFullWeekToggle($showShowFullWeekToggle);

	/**
	 * @abstract
	 * @return bool
	 */
	public function GetShowFullWeek();

	/**
	 * @param ScheduleLayoutSerializable $layoutResponse
	 */
	public function SetLayoutResponse($layoutResponse);

	/**
	 * @return string
	 */
	public function GetLayoutDate();

	/**
	 * @param int $scheduleId
	 * @return string|ScheduleDirection
	 */
	public function GetScheduleDirection($scheduleId);

	/**
	 * @param string|ScheduleDirection Direction
	 */
	public function SetScheduleDirection($direction);
        
        function AddItem(DashboardItem $item);
}

class ScheduleDirection
{
	const vertical = 'vertical';
	const horizontal = 'horizontal';
}

class SchedulePage extends ActionPage implements ISchedulePage
{
	protected $scheduleDirection = ScheduleDirection::horizontal;

	/**
	 * @var SchedulePresenter
	 */
	protected $_presenter;

	public function __construct()
	{
		parent::__construct('Schedule');

		$permissionServiceFactory = new PermissionServiceFactory();
		$scheduleRepository = new ScheduleRepository();
		$resourceService = new ResourceService(new ResourceRepository(), $permissionServiceFactory->GetPermissionService());
		$pageBuilder = new SchedulePageBuilder();
		$reservationService = new ReservationService(new ReservationViewRepository(), new ReservationListingFactory());
		$dailyLayoutFactory = new DailyLayoutFactory();
		$this->_presenter = new SchedulePresenter($this, $scheduleRepository, $resourceService, $pageBuilder, $reservationService, $dailyLayoutFactory);
	}

	public function ProcessPageLoad()
	{
		$start = microtime(true);

		$user = ServiceLocator::GetServer()->GetUserSession();

		$this->_presenter->PageLoad($user);

		$endLoad = microtime(true);

		$this->Set('SlotLabelFactory', $user->IsAdmin ? new AdminSlotLabelFactory() : new SlotLabelFactory());
		$this->Set('DisplaySlotFactory', new DisplaySlotFactory());
                $this->Set('items', $this->items);
		if ($this->scheduleDirection == ScheduleDirection::horizontal)
		{
			$this->Display('schedule.tpl');
		}
		else
		{
			$this->Display('schedule-flipped.tpl');
		}

		$endDisplay = microtime(true);

		$load = $endLoad - $start;
		$display = $endDisplay - $endLoad;
		Log::Debug('Schedule took %s sec to load, %s sec to render', $load, $display);
	}

	public function ProcessDataRequest($dataRequest)
	{
		$this->_presenter->GetLayout(ServiceLocator::GetServer()->GetUserSession());
	}

	public function GetScheduleId()
	{
		return $this->GetQuerystring(QueryStringKeys::SCHEDULE_ID);
	}

	public function SetScheduleId($scheduleId)
	{
		$this->Set('ScheduleId', $scheduleId);
	}

	public function SetScheduleName($scheduleName)
	{
		$this->Set('ScheduleName', $scheduleName);
	}

	public function SetSchedules($schedules)
	{
		$this->Set('Schedules', $schedules);
	}

	public function SetFirstWeekday($firstWeekday)
	{
		$this->Set('FirstWeekday', $firstWeekday);
	}

	public function SetResources($resources)
	{
		$this->Set('Resources', $resources);
	}

	public function SetDailyLayout($dailyLayout)
	{
		$this->Set('DailyLayout', $dailyLayout);
	}

	public function SetDisplayDates($dateRange)
	{
		$this->Set('DisplayDates', $dateRange);
		$this->Set('BoundDates', $dateRange->Dates());
	}

	public function SetPreviousNextDates($previousDate, $nextDate)
	{
		$this->Set('PreviousDate', $previousDate);
		$this->Set('NextDate', $nextDate);
	}

	public function GetSelectedDate()
	{
		// TODO: Clean date
		return $this->server->GetQuerystring(QueryStringKeys::START_DATE);
	}

	public function ShowInaccessibleResources()
	{
		return Configuration::Instance()->GetSectionKey(ConfigSection::SCHEDULE,
														ConfigKeys::SCHEDULE_SHOW_INACCESSIBLE_RESOURCES,
														new BooleanConverter());
	}

	public function ShowFullWeekToggle($showShowFullWeekToggle)
	{
		$this->Set('ShowFullWeekLink', $showShowFullWeekToggle);
	}

	public function GetShowFullWeek()
	{
		$showFullWeek = $this->GetQuerystring(QueryStringKeys::SHOW_FULL_WEEK);

		return !empty($showFullWeek);
	}

	public function ProcessAction()
	{
		// no-op
	}

	public function SetLayoutResponse($layoutResponse)
	{
		$this->SetJson($layoutResponse);
	}

	public function GetLayoutDate()
	{
		return $this->GetQuerystring(QueryStringKeys::LAYOUT_DATE);
	}

	public function GetScheduleDirection($scheduleId)
	{
		$cookie = $this->server->GetCookie("schedule-direction-$scheduleId");
		if ($cookie != null)
		{
			return $cookie;
		}

		return ScheduleDirection::horizontal;
	}

	public function SetScheduleDirection($direction)
	{
		$this->scheduleDirection = $direction;
		$this->Set('CookieName', 'schedule-direction-' . $this->GetVar('ScheduleId'));
		$this->Set('CookieValue', $direction == ScheduleDirection::vertical ? ScheduleDirection::horizontal : ScheduleDirection::vertical);
	}
	
	public function AddItem(DashboardItem $item)
	{
		$this->items[] = $item;
	}
}

class DisplaySlotFactory
{
	public function GetFunction(IReservationSlot $slot, $accessAllowed = false)
	{
		$slot->IsPending();
		if ($slot->IsReserved())
		{
			if ($this->IsMyReservation($slot))
			{
				return 'displayMyReserved';
			}
			else
			{
				return 'displayReserved';
			}
		}
		else
		{
			if (!$accessAllowed)
			{
				return 'displayRestricted';
			}
			else
			{
				if ($slot->IsPastDate(Date::Now()) && !$this->UserHasAdminRights())
				{
					return 'displayPastTime';
				}
				else
				{
					if ($slot->IsReservable())
					{
						return 'displayReservable';
					}
					else
					{
						return 'displayUnreservable';
					}
				}
			}
		}

		return null;
	}

	private function UserHasAdminRights()
	{
		return ServiceLocator::GetServer()->GetUserSession()->IsAdmin;
	}

	private function IsMyReservation(IReservationSlot $slot)
	{
		$mySession = ServiceLocator::GetServer()->GetUserSession();
		return $slot->IsOwnedBy($mySession);
	}
}

?>