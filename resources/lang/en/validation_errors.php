<?php
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
return [
    'promo_code_delete_already_sent'     => 'Cannot delete a code that has been already sent.',
    'promo_code_delete_already_redeemed' => 'Cannot delete a code that has been already redeemed.',
    'promo_code_email_send_already_sent' => 'Cannot resend a code that has been already sent.',
    'promo_code_email_send_empty_email'  => 'Cannot find an email address for the promocode owner.',
    'promo_code_email_send_empty_name'   => 'Cannot find a name for the promocode owner.',
    'speaker_assistance_delete_already_confirmed' => 'Cannot delete summit assistance :assistance_id because is already confirmed by speaker :speaker_id',
    'add_speaker_assistance_speaker_already_has_assistance' => 'speaker id :speaker_id already has an assistance for summit id :summit_id',
    'add_speaker_assistance_speaker_is_not_on_summit' => 'speaker id :speaker_id is not related to summit id :summit_id ( is not assigned as speaker of any published presentations nor its assigned as moderator)',
    'speaker_assistance_does_not_belongs_to_summit' => 'summit assistance :assistance_id does not belongs to summit :summit_id',
    'send_speaker_summit_assistance_announcement_mail_email_already_sent' => 'speaker announcement email was already sent for speaker :speaker_id on summit :summit_id',
    'send_speaker_summit_assistance_announcement_mail_run_out_promo_code' => 'can not get any promo code for speaker :speaker_id , run out of :type promo codes for summit :summit_id',
    'send_speaker_summit_assistance_announcement_mail_code_already_redeemed' => 'promo code :promo_code already redeemed.',
    'send_speaker_summit_assistance_announcement_mail_invalid_mail_type' => 'mail type :mail_type is not valid.',
    'send_speaker_summit_assistance_promo_code_not_set' => 'speaker :speaker_id has not set a promo code for summit :summit_id, please set one manually.',
    // LocationService
    'LocationService.addLocation.LocationNameAlreadyExists' => 'there is already another location with same name for summit :summit_id',
    'LocationService.addLocation.InvalidClassName' => 'invalid class name',
    'LocationService.addLocation.InvalidAddressOrCoordinates' => 'was passed a non-existent address',
    'LocationService.addLocation.OverQuotaLimit' => 'geocode api over rate limit, try again later',
    'LocationService.addLocation.geoCodingGenericError' => 'geocode api generic error',
    'LocationService.updateLocation.LocationNameAlreadyExists' => 'there is already another location with same name for summit :summit_id',
    'LocationService.updateLocation.LocationNotFoundOnSummit' => 'location :location_id not found on summit :summit_id',
    'LocationService.updateLocation.ClassNameMissMatch' => 'location :location_id on summit :summit_id does not belongs to class name :class_name',
    'LocationService.addVenueFloor.FloorNameAlreadyExists' => 'floor name :floor_name already belongs to another floor on venue :venue_id',
    'LocationService.addVenueFloor.FloorNumberAlreadyExists' => 'floor number :floor_number already belongs to another floor on venue :venue_id',
    'LocationService.updateVenueFloor.FloorNameAlreadyExists' => 'floor name :floor_name already belongs to another floor on venue :venue_id',
    'LocationService.updateVenueFloor.FloorNumberAlreadyExists' => 'floor number :floor_number already belongs to another floor on venue :venue_id',
    'LocationService.addVenueRoom.InvalidClassName' => 'invalid class name',
    'LocationService.addVenueRoom.LocationNameAlreadyExists' => 'there is already another location with same name for summit :summit_id',
    'LocationService.updateVenueRoom.LocationNameAlreadyExists' => 'there is already another location with same name for summit :summit_id',
    'LocationService.addLocationBanner.InvalidClassName' => 'invalid class name',
    'LocationService.addLocationMap.FileNotAllowedExtension' => 'file extension is not allowed (:allowed_extensions)',
    'LocationService.addLocationMap.FileMaxSize' => 'file exceeds max_file_size (:max_file_size MB)',
    'LocationService.updateLocationMap.FileNotAllowedExtension' => 'file extension is not allowed (:allowed_extensions)',
    'LocationService.updateLocationMap.FileMaxSize' => 'file exceeds max_file_size (:max_file_size MB)',
    'LocationService.addLocationImage.FileNotAllowedExtension' => 'file extension is not allowed (:allowed_extensions)',
    'LocationService.addLocationImage.FileMaxSize' => 'file exceeds max_file_size (:max_file_size MB)',
    'LocationService.updateLocationImage.FileNotAllowedExtension' => 'file extension is not allowed (:allowed_extensions)',
    'LocationService.updateLocationImage.FileMaxSize' => 'file exceeds max_file_size (:max_file_size MB)',
    // RSVPTemplateService
    'RSVPTemplateService.addQuestion.QuestionNameAlreadyExists' => 'question name :name already exists for template :template_id',
    'RSVPTemplateService.updateQuestion.QuestionNameAlreadyExists' => 'question name :name already exists for template :template_id',
    'RSVPTemplateService.addQuestionValue.ValueAlreadyExist' => 'value :value already exists on question :question_id',
    'RSVPTemplateService.addTemplate.TitleAlreadyExists' => 'title :title already exists on summit :summit_id',
    'RSVPTemplateService.updateTemplate.TitleAlreadyExists' => 'title :title already exists on summit :summit_id',
    // SummitTicketTypeService
    'SummitTicketTypeService.addTicketType.NameAlreadyExists' => 'ticket name :name already exists on summit :summit_id',
    'SummitTicketTypeService.addTicketType.ExternalIdAlreadyExists' => 'ticket external id :external_id already exists on summit :summit_id',
    'SummitTicketTypeService.updateTicketType.NameAlreadyExists' => 'ticket name :name already exists on summit :summit_id',
    'SummitTicketTypeService.updateTicketType.ExternalIdAlreadyExists' => 'ticket external id :external_id already exists on summit :summit_id',
    'SummitTicketTypeService.seedSummitTicketTypesFromEventBrite.MissingExternalId' => 'summit :summit_is has not set external id (eventbrite)',
    // PresentationCategoryGroupService
    'PresentationCategoryGroupService.addTrackGroup.NameAlreadyExists' => 'name :name already exists for summit :summit_id',


];