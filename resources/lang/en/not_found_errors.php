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
    'LocationService.deleteVenueFloor.FloorNotFound' => 'floor :floor_id does not belongs to venue :venue_id',
    'LocationService.deleteVenueFloor.VenueNotFound' => 'venue :venue_id not found on summit :summit_id',
    'LocationService.addVenueRoom.FloorNotFound' => 'floor :floor_id does not belongs to venue :venue_id',
    'LocationService.addVenueRoom.VenueNotFound' => 'venue :venue_id not found on summit :summit_id',
    'LocationService.updateVenueRoom.FloorNotFound' => 'floor :floor_id does not belongs to venue :venue_id',
    'LocationService.updateVenueRoom.VenueNotFound' => 'venue :venue_id not found on summit :summit_id',
    'LocationService.addLocationBanner.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.deleteLocationBanner.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.deleteLocationBanner.BannerNotFound'=> 'banner :banner_id not found on location :location_id',
    'LocationService.updateLocationBanner.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.updateLocationBanner.BannerNotFound'=> 'banner :banner_id not found on location :location_id',
    'LocationService.addLocationMap.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.updateLocationMap.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.updateLocationMap.MapNotFound' => 'map :map_id does not belongs to location :location_id',
    'LocationService.deleteLocationMap.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.deleteLocationMap.MapNotFound' => 'map :map_id not found on location :location_id',
    'LocationService.addLocationImage.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.updateLocationImage.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.updateLocationImage.ImageNotFound' => 'image :image_id does not belongs to location :location_id',
    'LocationService.deleteLocationImage.LocationNotFound' => 'location :location_id not found on summit :summit_id',
    'LocationService.deleteLocationImage.ImageNotFound' => 'image :image_id not found on location :location_id',
    // RSVPTemplateService
    'RSVPTemplateService.updateTemplate.TemplateNotFound' => 'template :template_id not found on summit :summit_id',
    'RSVPTemplateService.deleteTemplate.TemplateNotFound' => 'template :template_id not found on summit :summit_id',
    'RSVPTemplateService.addQuestion.TemplateNotFound'    => 'template :template_id not found on summit :summit_id',
    'RSVPTemplateService.updateQuestion.TemplateNotFound' => 'template :template_id not found on summit :summit_id',
    'RSVPTemplateService.updateQuestion.QuestionNotFound' => 'question :question_id not found on template :template_id',
    'RSVPTemplateService.deleteQuestion.TemplateNotFound' => 'template :template_id not found on summit :summit_id',
    'RSVPTemplateService.deleteQuestion.QuestionNotFound' => 'question :question_id not found on template :template_id',
    'RSVPTemplateService.addQuestionValue.TemplateNotFound' => 'template :template_id not found on summit :summit_id',
    'RSVPTemplateService.addQuestionValue.QuestionNotFound' => 'question :question_id not found on template :template_id',
    'RSVPTemplateService.deleteQuestionValue.TemplateNotFound' => 'template :template_id not found on summit :summit_id',
    'RSVPTemplateService.deleteQuestionValue.QuestionNotFound' => 'question :question_id not found on template :template_id',
    'RSVPTemplateService.deleteQuestionValue.ValueNotFound' => 'value :value_id not found on question :question_id',
    // SummitTicketTypeService
    'SummitTicketTypeService.updateTicketType.TicketTypeNotFound' => 'ticket type :ticket_type_id does not exists on summit :summit_id',
    'SummitTicketTypeService.deleteTicketType.TicketTypeNotFound' => 'ticket type :ticket_type_id does not exists on summit :summit_id',
    // PresentationCategoryGroupService
    'PresentationCategoryGroupService.updateTrackGroup.TrackGroupNotFound' => 'track group :track_group_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.deleteTrackGroup.TrackGroupNotFound' => 'track group :track_group_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.associateTrack2TrackGroup.TrackGroupNotFound' => 'track group :track_group_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.associateTrack2TrackGroup.TrackNotFound' => 'track  :track_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.disassociateTrack2TrackGroup.TrackGroupNotFound' => 'track group :track_group_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.disassociateTrack2TrackGroup.TrackNotFound' => 'track  :track_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.associateAllowedGroup2TrackGroup.TrackGroupNotFound' => 'track group :track_group_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.associateAllowedGroup2TrackGroup.GroupNotFound' => 'group :group_id does not exists.',
    'PresentationCategoryGroupService.disassociateAllowedGroup2TrackGroup.TrackGroupNotFound' => 'track group :track_group_id does not exists on summit :summit_id',
    'PresentationCategoryGroupService.disassociateAllowedGroup2TrackGroup.GroupNotFound' => 'group :group_id does not exists.',
    'SummitService.updateSummit.SummitNotFound' => 'summit :summit_id not found',
    'SummitService.deleteSummit.SummitNotFound' => 'summit :summit_id not found',
    // SummitPushNotificationService
    'SummitPushNotificationService.addPushNotification.EventNotFound' => 'event :event_id does not belongs to summit :summit_id schedule',
    'SummitPushNotificationService.addPushNotification.GroupNotFound' => 'group :group_id not found',
    'SummitPushNotificationService.addPushNotification.MemberNotFound' => 'member :member_id not found',
    'SummitPushNotificationService.approveNotification.NotificationNotFound' => 'notification :notification_id not found on summit :summit_id',
    'SummitPushNotificationService.unApproveNotification.NotificationNotFound'=> 'notification :notification_id not found on summit :summit_id',
    'SummitPushNotificationService.deleteNotification.NotificationNotFound'=> 'notification :notification_id not found on summit :summit_id',
    'SummitSelectionPlanService.updateSelectionPlan.SelectionPlanNotFound' => 'selection plan :selection_plan_id not found on summit :summit_id',
    'SummitSelectionPlanService.deleteSelectionPlan.SelectionPlanNotFound' => 'selection plan :selection_plan_id not found on summit :summit_id',
    'SummitSelectionPlanService.addTrackGroupToSelectionPlan.SelectionPlanNotFound' => 'selection plan :selection_plan_id not found on summit :summit_id',
    'SummitSelectionPlanService.addTrackGroupToSelectionPlan.TrackGroupNotFound' => 'track group :track_group_id not found on summit :summit_id',
    'SummitSelectionPlanService.deleteTrackGroupToSelectionPlan.SelectionPlanNotFound' => 'selection plan :selection_plan_id not found on summit :summit_id',
    'SummitSelectionPlanService.deleteTrackGroupToSelectionPlan.TrackGroupNotFound' => 'track group :track_group_id not found on summit :summit_id',
    // Presentations
    'PresentationService.saveOrUpdatePresentation.trackNotFound' => 'track :track_id not found.',
    'PresentationService.saveOrUpdatePresentation.eventTypeNotFound' => 'event type :type_id not found.',
    'PresentationService.saveOrUpdatePresentation.trackQuestionNotFound' => 'extra question :question_id not found.',
    'PresentationService.updatePresentationSubmission.PresentationNotFound' => 'presentation :presentation_id not found',
    // track tag groups
    'SummitTrackTagGroupService.updateTrackTagGroup.TrackTagGroupNotFound' => 'track tag group :track_tag_group_id not found on summit :summit_id',
    'SummitTrackTagGroupService.deleteTrackTagGroup.TrackTagGroupNotFound' => 'track tag group :track_tag_group_id not found on summit :summit_id',
    'SummitTrackTagGroupService.seedTagOnAllTrack.TagNotFound' => 'tag :tag_id not found',
    'SummitTrackTagGroupService.seedTagTrackGroupTagsOnTrack.TrackTagGroupNotFound' =>  'track tag group :track_tag_group_id not found on summit :summit_id',
    'SummitTrackTagGroupService.seedTagTrackGroupTagsOnTrack.TrackNotFound' => 'track :track_id not found on summit :summit_id',
    // track question templates
    'TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackQuestionTemplateNotFound' => 'track question template :track_question_template_id not found',
    'TrackQuestionTemplateService.deleteTrackQuestionTemplate.TrackQuestionTemplateNotFound' => 'track question template :track_question_template_id not found',
    'TrackQuestionTemplateService.addTrackQuestionValueTemplate.TrackQuestionTemplateNotFound' => 'track question template :track_question_template_id not found',
    'TrackQuestionTemplateService.updateTrackQuestionValueTemplate.TrackQuestionTemplateNotFound' => 'track question template :track_question_template_id not found',
    'TrackQuestionTemplateService.updateTrackQuestionValueTemplate.TrackQuestionTemplateValueNotFound' => 'track question template value :track_question_value_template_id not found',
    'TrackQuestionTemplateService.deleteTrackQuestionValueTemplate.TrackQuestionTemplateNotFound' => 'track question template :track_question_template_id not found',
    'TrackQuestionTemplateService.deleteTrackQuestionValueTemplate.TrackQuestionTemplateValueNotFound' => 'track question template value :track_question_value_template_id not found',
    'TrackQuestionTemplateService.addTrackQuestionTemplate.TrackNotFound' => 'track :track_id not found',
    'TrackQuestionTemplateService.updateTrackQuestionTemplate.TrackNotFound' => 'track :track_id not found',
    'TrackQuestionTemplateService.updateTrackQuestionTemplate.DefaultValueNotFound' => 'default value :default_value not found',
    // tracks
    'SummitTrackService.addTrackExtraQuestion.TrackNotFound' => 'track :track_id not found',
    'SummitTrackService.addTrackExtraQuestion.QuestionNotFound' => 'question :question_id not found',
    'SummitTrackService.removeTrackExtraQuestion.TrackNotFound' => 'track :track_id not found',
    'SummitTrackService.removeTrackExtraQuestion.QuestionNotFound' => 'question :question_id not found',
];