<?php namespace App\Services\Model;

/**
 * Copyright 2018 OpenStack Foundation
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * http://www.apache.org/licenses/LICENSE-2.0
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 **/
use App\Models\Foundation\Summit\Factories\SummitPushNotificationFactory;
use libs\utils\ITransactionService;
use models\exceptions\EntityNotFoundException;
use models\exceptions\ValidationException;
use models\main\IGroupRepository;
use models\main\IMemberRepository;
use models\main\Member;
use models\summit\Summit;
use models\summit\SummitPushNotification;
use models\summit\SummitPushNotificationChannel;

/**
 * Class SummitPushNotificationService
 * @package App\Services\Model
 */
final class SummitPushNotificationService
    extends AbstractService
    implements ISummitPushNotificationService
{

    /**
     * @var IGroupRepository
     */
    private $group_repository;

    /**
     * @var IMemberRepository
     */
    private $member_repository;

    /**
     * SummitPushNotificationService constructor.
     * @param IGroupRepository $group_repository
     * @param IMemberRepository $member_repository
     * @param ITransactionService $tx_service
     */
    public function __construct
    (
        IGroupRepository $group_repository,
        IMemberRepository $member_repository,
        ITransactionService $tx_service

    )
    {
        parent::__construct($tx_service);
        $this->group_repository = $group_repository;
        $this->member_repository = $member_repository;
    }

    /**
     * @param Summit $summit
     * @param Member|null $current_member
     * @param array $data
     * @return SummitPushNotification
     * @throws ValidationException
     * @throws EntityNotFoundException
     */
    public function addPushNotification(Summit $summit, Member $current_member, array $data)
    {
        return $this->tx_service->transaction(function () use ($summit, $current_member, $data) {
            $params = [];
            if (!is_null($current_member))
                $params['owner'] = $current_member;

            if (isset($data['event_id'])) {
                $event = $summit->getScheduleEvent(intval($data['event_id']));
                if (is_null($event)) {
                    throw new EntityNotFoundException
                    (
                        trans
                        (
                            'not_found_errors.SummitPushNotificationService.addPushNotification.EventNotFound',
                            [
                                'summit_id' => $summit->getId(),
                                'event_id'  => $data['event_id']
                            ]
                        )
                    );
                }
                $params['event'] = $event;
            }

            if (isset($data['group_id'])) {
                $group = $this->group_repository->getById(intval($data['group_id']));
                if (is_null($group)) {
                    throw new EntityNotFoundException
                    (
                        trans
                        (
                            'not_found_errors.SummitPushNotificationService.addPushNotification.GroupNotFound',
                            [
                                'summit_id' => $summit->getId(),
                                'group_id'  => $data['group_id']
                            ]
                        )
                    );
                }
                $params['group'] = $group;
            }

            if (isset($data['recipient_ids'])) {

                $recipients = [];
                foreach ($data['recipient_ids'] as $recipient_id) {
                    $recipient = $this->member_repository->getById(intval($recipient_id));

                    if (is_null($recipient)) {
                        throw new EntityNotFoundException
                        (
                            'not_found_errors.SummitPushNotificationService.addPushNotification.MemberNotFound',
                            [
                                'summit_id' => $summit->getId(),
                                'member_id' => $recipient_id
                            ]
                        );
                    }

                    if(!$recipient->isActive()){
                        throw new ValidationException
                        (
                            trans
                            (
                                'validation_errors.SummitPushNotificationService.addPushNotification.MemberNotActive',
                                [
                                    'summit_id' => $summit->getId(),
                                    'member_id' => $recipient_id
                                ]
                            )
                        );
                    }
                    $recipients[] = $recipient;
                }

                $params['recipients'] = $recipients;

            }

            $notification = SummitPushNotificationFactory::build($summit, $data, $params);

            if($notification->getChannel() == SummitPushNotificationChannel::Members){
                // auto approve for members
                $notification->setApproved(true);
                if(!is_null($current_member))
                    $notification->setApprovedBy($current_member);
            }

            $summit->addNotification($notification);

            return $notification;

        });
    }
}