<?php
/**
 * Copyright 2017 OpenStack Foundation
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
return [
    'promo_code_delete_code_not_found'                                      => 'promo code id :promo_code_id does not belongs to summit id :summit_id.',
    'promo_code_email_code_not_found'                                       => 'promo code id :promo_code_id does not belongs to summit id :summit_id.',
    'add_speaker_assistance_speaker_not_found'                              => 'speaker id :speaker_id not found',
    'send_speaker_summit_assistance_announcement_mail_not_found_assistance' => 'summit speaker assistance :assistance_id not found for summit id :summit_id',
    // LocationService
    'LocationService.addVenueFloor.VenueNotFound'    => 'venue :venue_id not found on summit :summit_id',
    'LocationService.updateVenueFloor.FloorNotFound' => 'floor :floor_id does not belongs to venue :venue_id',
    'LocationService.updateVenueFloor.VenueNotFound' => 'venue :venue_id not found on summit :summit_id',
];