<?php

namespace app\modules\bot\components\api\Types;

use TelegramBot\Api\Types\Animation;
use TelegramBot\Api\Types\ArrayOfMessageEntity;
use TelegramBot\Api\Types\ArrayOfPhotoSize;
use TelegramBot\Api\Types\ArrayOfUser;
use TelegramBot\Api\Types\Audio;
use TelegramBot\Api\Types\Contact;
use TelegramBot\Api\Types\Dice;
use TelegramBot\Api\Types\Document;
use TelegramBot\Api\Types\Location;
use TelegramBot\Api\Types\Payments\Invoice;
use TelegramBot\Api\Types\Payments\SuccessfulPayment;
use TelegramBot\Api\Types\Poll;
use TelegramBot\Api\Types\Sticker;
use TelegramBot\Api\Types\User;
use TelegramBot\Api\Types\Venue;
use TelegramBot\Api\Types\Video;
use TelegramBot\Api\Types\VideoChatEnded;
use TelegramBot\Api\Types\VideoChatParticipantsInvited;
use TelegramBot\Api\Types\VideoChatScheduled;
use TelegramBot\Api\Types\VideoChatStarted;
use TelegramBot\Api\Types\Voice;

class Message extends \TelegramBot\Api\Types\Message
{
    // A message can only be deleted if it was sent less than 48 hours ago
    public const DELETE_MESSAGE_LIFETIME = 2 * 24 * 60 * 60; // seconds

    /**
     * {@inheritdoc}
     *
     * @var array
     */
    protected static $map = [
        'message_id' => true,
        'from' => User::class,
        'date' => true,
        'chat' => Chat::class,
        'forward_from' => User::class,
        'forward_from_chat' => Chat::class,
        'forward_from_message_id' => true,
        'forward_date' => true,
        'forward_signature' => true,
        'forward_sender_name' => true,
        'reply_to_message' => Message::class,
        'edit_date' => true,
        'media_group_id' => true,
        'author_signature' => true,
        'text' => true,
        'entities' => ArrayOfMessageEntity::class,
        'caption_entities' => ArrayOfMessageEntity::class,
        'audio' => Audio::class,
        'document' => Document::class,
        'animation' => Animation::class,
        'photo' => ArrayOfPhotoSize::class,
        'sticker' => Sticker::class,
        'video' => Video::class,
        'voice' => Voice::class,
        'caption' => true,
        'contact' => Contact::class,
        'location' => Location::class,
        'venue' => Venue::class,
        'poll' => Poll::class,
        'dice' => Dice::class,
        'new_chat_members' => ArrayOfUser::class,
        'left_chat_member' => User::class,
        'new_chat_title' => true,
        'new_chat_photo' => ArrayOfPhotoSize::class,
        'delete_chat_photo' => true,
        'group_chat_created' => true,
        'supergroup_chat_created' => true,
        'channel_chat_created' => true,
        'migrate_to_chat_id' => true,
        'migrate_from_chat_id' => true,
        'pinned_message' => Message::class,
        'invoice' => Invoice::class,
        'successful_payment' => SuccessfulPayment::class,
        'connected_website' => true,
        'video_chat_scheduled' => VideoChatScheduled::class,
        'video_chat_started' => VideoChatStarted::class,
        'video_chat_ended' => VideoChatEnded::class,
        'video_chat_participants_invited' => VideoChatParticipantsInvited::class,
    ];

    /**
    * @return bool
    */
    public function canDelete()
    {
        return $this->getDate() > (time() - self::DELETE_MESSAGE_LIFETIME);
    }

    /**
    * @return bool
    */
    public function isNew()
    {
        return !$this->isEdit() && !$this->isForward() && !$this->isReply();
    }

    /**
    * @return bool
    */
    public function isEdit()
    {
        return (bool)$this->getEditDate();
    }

    /**
    * @return bool
    */
    public function isForward()
    {
        return (bool)$this->getForwardFrom();
    }

    /**
    * @return bool
    */
    public function isReply()
    {
        return (bool)$this->getReplyToMessage();
    }
}
