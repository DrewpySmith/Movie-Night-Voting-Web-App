<?php

namespace App\Enums;

enum NotificationType: string
{
    case VoteReceived = 'vote_received';
    case NewMemberJoined = 'new_member_joined';
    case InvitationCreated = 'invitation_created';
    case InvitationAccepted = 'invitation_accepted';
    case WinnerDeclared = 'winner_declared';
}
