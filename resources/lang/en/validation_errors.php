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
    // SummitService
    'SummitService.AddSummit.NameAlreadyExists' => 'name :name its already being assigned to another summit',
    'SummitService.updateSummit.NameAlreadyExists'=> 'name :name its already being assigned to another summit',
    'SummitService.updateSummit.SummitAlreadyActive' => 'summit :active_summit_id is already activated please deactivate it to set current summit as active',
    'SummitTrackService.copyTracks.SameSummit' => 'from summit is equal a to summit.',
    // SummitPushNotificationService
    'SummitPushNotificationService.addPushNotification.MemberNotActive' => 'member :member_id is not active',
    'SummitPushNotificationService.deleteNotification.NotificationAlreadySent' => 'notification :notification_id is already sent.',
    'Summit.checkSelectionPlanConflicts.conflictOnSelectionWorkflow' => 'there is a conflict on selection dates with selection plan :selection_plan_id on summit :summit_id',
    'Summit.checkSelectionPlanConflicts.conflictOnSubmissionWorkflow' => 'there is a conflict on submission dates with selection plan :selection_plan_id on summit :summit_id',
    'Summit.checkSelectionPlanConflicts.conflictOnVotingWorkflow' => 'there is a conflict on voting dates with selection plan :selection_plan_id on summit :summit_id',
    'SummitSelectionPlanService.addSelectionPlan.alreadyExistName' => 'there is already another selection plan with same name on summit :summit_id',
    'SummitSelectionPlanService.updateSelectionPlan.alreadyExistName' => 'there is already another selection plan with same name on summit :summit_id',
    // Presentations
    'PresentationService.saveOrUpdatePresentation.invalidPresentationType' => 'type id :type_id is not a valid presentation type',
    'PresentationService.saveOrUpdatePresentation.notAvailableCFP' => 'type id :type_id is not a available for CFP',
    'PresentationService.saveOrUpdatePresentation.trackDontBelongToSelectionPlan' => 'track :track_id does not belongs to selection plan :selection_plan_id',
    'PresentationService.submitPresentation.limitReached' => 'You reached the limit :limit of presentations.',
    'PresentationService.saveOrUpdatePresentation.MaxAllowedLinks' => 'max. links quantity allowed is :max_allowed_links.',
    'PresentationService.submitPresentation.NotValidSpeaker' => 'Current Member not has a valid speaker profile',
    'PresentationService.submitPresentation.NotValidSelectionPlan' => 'Current Summit not has a valid selection plan',
    'PresentationService.updatePresentationSubmission.NotValidSpeaker' => 'Current Member not has a valid speaker profile',
    'PresentationService.updatePresentationSubmission.NotValidSelectionPlan' => 'Current Summit not has a valid selection plan',
    'PresentationService.updatePresentationSubmission.CurrentSpeakerCanNotEditPresentation' => 'Current Speaker can not edit :presentation_id presentation',
    'PresentationService.saveOrUpdatePresentation.TagNotAllowed' => 'tag :tag is not allowed on track :track_id',
    // organizations
    'OrganizationService.addOrganization.alreadyExistName' => 'Organization name :name already exists!',
    // track tag groups
    'SummitTrackTagGroupService.addTrackTagGroup.TrackTagGroupLabelAlreadyExists' => 'track tag group label already exist on summit :summit_id',
    'SummitTrackTagGroupService.addTrackTagGroup.TrackTagGroupNameAlreadyExists' => 'track tag group name already exist on summit :summit_id',
    'SummitTrackTagGroupService.updateTrackTagGroup.TrackTagGroupLabelAlreadyExists' => 'track tag group label already exist on summit :summit_id',
    'SummitTrackTagGroupService.updateTrackTagGroup.TrackTagGroupNameAlreadyExists' => 'track tag group name already exist on summit :summit_id',
    'SummitTrackTagGroupService.seedTagOnAllTrack.TagDoesNotBelongToTrackTagGroup' => 'tag :tag_id does not belongs to any track tag group on summit :summit_id',
    // track question templates
    'TrackQuestionTemplateService.addTrackQuestionTemplate.TrackQuestionTemplateLabelAlreadyExist' => 'track question template label already exists',
    'TrackQuestionTemplateService.addTrackQuestionTemplate.TrackQuestionTemplateNameAlreadyExist' => 'track question template name already exists',
    'TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackQuestionTemplateLabelAlreadyExist' => 'track question template label already exists',
    'TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackQuestionTemplateNameAlreadyExist' => 'track question template name already exists',
    'TrackQuestionTemplateService.addTrackQuestionValueTemplate.ValueAlreadyExist' => 'value :value already exists on track question template :track_question_template_id',
    'TrackQuestionTemplateService.addTrackQuestionValueTemplate.LabelAlreadyExist' => 'label :label already exists on track question template :track_question_template_id',
    'TrackQuestionTemplateService.updateTrackQuestionValueTemplate.ValueAlreadyExist' => 'value :value already exists on track question template :track_question_template_id',
    'TrackQuestionTemplateService.updateTrackQuestionValueTemplate.LabelAlreadyExist' => 'label :label already exists on track question template :track_question_template_id'
];