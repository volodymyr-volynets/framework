<?php

/*
 * This file is part of Numbers Framework.
 *
 * (c) Volodymyr Volynets <volodymyr.volynets@gmail.com>
 *
 * This source file is subject to the Apache 2.0 license that is bundled
 * with this source code in the file LICENSE.
 */

namespace NF;

use Object\Content\LocalizationConstants;

class Message extends LocalizationConstants
{
    public static $prefix = 'NF.MS.';
    public const ADD_TO_CONTEXT_COLON = ['NF.Message.AddToContextColon' => 'Add to context:','errno' => 'NF.MS.0063'];
    public const ADD_TO_CONTEXT_VALUE = ['NF.Message.AddToContextValue' => 'Add to context: {value}','errno' => 'NF.MS.0062'];
    public const A_FILTER_IS_REQUIRED_ON_ANY_FIELD = ['NF.Message.AFilterIsRequiredOnAnyField' => 'A filter is required on any field!','errno' => 'NF.MS.0059'];
    public const BEARER_TOKEN_SESSION_EXPIRED = ['NF.Message.BearerTokenSessionExpired' => 'Bearer token/session expired!','errno' => 'NF.MS.0014'];
    public const CALLS_SUBSCRIBERS_AS_PER_CRON_EXPRESSION = ['NF.Message.CallsSubscribersAsPerCronExpression' => 'Calls subscribers as per cron expression.','errno' => 'NF.MS.0033'];
    public const CALLS_SUBSCRIBERS_AS_PER_CRON_EXPRESSION_DOT = ['NF.Message.CallsSubscribersAsPerCronExpressionDot' => 'Calls subscribers as per cron expression.','errno' => 'NF.MS.0048'];
    public const CALLS_SUBSCRIBERS_AT_DATETIME = ['NF.Message.CallsSubscribersAtDatetime' => 'Calls subscribers at datetime.','errno' => 'NF.MS.0034'];
    public const CALLS_SUBSCRIBERS_AT_DATETIME_DOT = ['NF.Message.CallsSubscribersAtDatetimeDot' => 'Calls subscribers at datetime.','errno' => 'NF.MS.0049'];
    public const CALLS_SUBSCRIBERS_AT_THE_END_OF_A_REQUEST = ['NF.Message.CallsSubscribersAtTheEndOfARequest' => 'Calls subscribers at the end of a request.','errno' => 'NF.MS.0030'];
    public const CALLS_SUBSCRIBERS_AT_THE_END_OF_A_REQUEST_DOT = ['NF.Message.CallsSubscribersAtTheEndOfARequestDot' => 'Calls subscribers at the end of a request.','errno' => 'NF.MS.0045'];
    public const CALLS_SUBSCRIBERS_EVERY5_SECONDS = ['NF.Message.CallsSubscribersEvery5Seconds' => 'Calls subscribers every 5 seconds.','errno' => 'NF.MS.0031'];
    public const CALLS_SUBSCRIBERS_EVERY5_SECONDS_DOT = ['NF.Message.CallsSubscribersEvery5SecondsDot' => 'Calls subscribers every 5 seconds.','errno' => 'NF.MS.0046'];
    public const CALLS_SUBSCRIBERS_EVERY_MINUTE_OR_SPECIFIED_INTEEERVAL = ['NF.Message.CallsSubscribersEveryMinuteOrSpecifiedInteeerval' => 'Calls subscribers every minute or specified inteeerval.','errno' => 'NF.MS.0032'];
    public const CALLS_SUBSCRIBERS_EVERY_MINUTE_OR_SPECIFIED_INTEEERVAL_DOT = ['NF.Message.CallsSubscribersEveryMinuteOrSpecifiedInteeervalDot' => 'Calls subscribers every minute or specified inteeerval.','errno' => 'NF.MS.0047'];
    public const CALLS_SUBSCRIBERS_IN_REALTIME = ['NF.Message.CallsSubscribersInRealtime' => 'Calls subscribers in realtime.','errno' => 'NF.MS.0029'];
    public const CALLS_SUBSCRIBERS_IN_REALTIME_DOT = ['NF.Message.CallsSubscribersInRealtimeDot' => 'Calls subscribers in realtime.','errno' => 'NF.MS.0044'];
    public const CAMPAIGN_COULD_NOT_DELIVER_MESSAGE = ['NF.Message.CampaignCouldNotDeliverMessage' => 'Campaign {id} contact {name} could not deliver message.','errno' => 'NF.MS.0057'];
    public const CAMPAIGN_HAS_CONTACTS_COUNTER = ['NF.Message.CampaignHasContactsCounter' => 'Campaign {id} has {counter} contact(s).','errno' => 'NF.MS.0052'];
    public const CONGRATULATIONS_YOU_HAVE_SIGN_OUT = ['NF.Message.CongratulationsYouHaveSignOut' => 'Congratulations! You have successfully signed out.','errno' => 'NF.MS.0050'];
    public const COULD_NOT_PROCESS_A_J_A_X_CALL = ['NF.Message.CouldNotProcessAJAXCall' => 'Could not process ajax call!','errno' => 'NF.MS.0058'];
    public const COULD_NOT_SEND_CAMPAIGN_MESSAGE = ['NF.Message.CouldNotSendCampaignMessage' => 'Could not send campaign {campaign_id} message to {email}!','errno' => 'NF.MS.0053'];
    public const CREDENTIALS_DO_NOT_MATCH = ['NF.Message.CredentialsDoNotMatch' => 'Provided credentials do not match our records!','errno' => 'NF.MS.0003'];
    public const DO_NOT_WANT_TO_RECEIVE_EMAILS = ['NF.Message.DoNotWantToReceiveEmails' => 'If you would no longer like to receive emails like this one please click here to {unsubscribe}.','errno' => 'NF.MS.0024'];
    public const DUPLICATE_RECORD = ['NF.Message.DuplicateRecord' => 'Duplicate record with selected values already exists!','errno' => 'NF.MS.0018'];
    public const EXECUTED_NUMBER_POSTPONED_EVENTS = ['NF.Message.ExecutedNumberPostponedEvents' => 'Executed {number} postponed events!'];
    public const GOOGLE_MAP_OF = ['NF.Message.GoogleMapOf' => 'Google map of {location}','errno' => 'NF.MS.0011'];
    public const LAST_AGO = ['NF.Message.LastAgo' => 'Last {ago}','errno' => 'NF.MS.0041'];
    public const LINK_VALID_FOR_HOURS = ['NF.Message.LinkValidForHours' => 'Link is valid for {hours} hours','errno' => 'NF.MS.0008'];
    public const LIST_CREATED_SUCCESSFULLY_CODE = ['NF.Message.ListCreatedSuccessfullyCode' => 'List created successfully: {code}!','errno' => 'NF.MS.0061'];
    public const LOGGED_IN_AS_NAME = ['NF.Message.LoggedInAsName' => 'Logged in as {name}','errno' => 'NF.MS.0020'];
    public const NEW_I_P_LOGIN = ['NF.Message.NewIPLogin' => 'New IP Login!','errno' => 'NF.MS.0012'];
    public const NEW_I_P_MESSAGE = ['NF.Message.NewIPMessage' => 'You are receiving this New IP Login Email because you logged in into {config://brand.name.welcome} system with new IP address.','errno' => 'NF.MS.0013'];
    public const NEW_LOGS = ['NF.Message.NewLogs' => 'New Logs!','errno' => 'NF.MS.0027'];
    public const NEW_LOGS_EXPLANATION = ['NF.Message.NewLogsExplanation' => 'You are receiving this New Logs Email because you are assigned admin.','errno' => 'NF.MS.0028'];
    public const NO_ROWS_FOUND = ['NF.Message.NoRowsFound' => '{errno}: No rows found!','errno' => 'NF.MS.0001'];
    public const OPERATION_EXECUTED_SUCCESSFULLY = ['NF.Message.OperationExecutedSuccessfully' => 'Operation has been successfully executed!','errno' => 'NF.MS.0060'];
    public const PASSWORD_RESET_MESSAGE = ['NF.Message.PasswordResetMessage' => 'You are receiving this Password Reset Email because you requested password reset in {config://brand.name.welcome} system.','errno' => 'NF.MS.0010'];
    public const PASSWORD_RESET_REQUEST = ['NF.Message.PasswordResetRequest' => 'Password Reset Request!','errno' => 'NF.MS.0009'];
    public const PLEASE_CHECK_YOUR_EMAIL_FOR_LINK = ['NF.Message.PleaseCheckYourEmailForLink' => 'Please check your email and click the link provided to reset your password.','errno' => 'NF.MS.0005'];
    public const REGISTERED_WITH_PROVIDER = ['NF.Message.RegisteredWithProvider' => 'Registered with {provider}!','errno' => 'NF.MS.0016'];
    public const REGISTERED_WITH_WEBSITE = ['NF.Message.RegisteredWithWebsite' => 'Registered with website form!','errno' => 'NF.MS.0017'];
    public const SENT_CAMPAIGN_MESSAGE_TO_EMAIL = ['NF.Message.SentCampaignMessageToEmail' => 'Sent campaign {campaign_id} message to {email}!','errno' => 'NF.MS.0054'];
    public const SIGN_IN_TO_CONTINUE_THE_GAME = ['NF.Message.SignInToContinueTheGame' => 'Sign in to continue the game.','errno' => 'NF.MS.0036'];
    public const SUCCESSFULLY_INVITED = ['NF.Message.SuccessfullyInvited' => 'Successfully invited!','errno' => 'NF.MS.0023'];
    public const SUCCESSFULLY_INVITED_AS_NAME = ['NF.Message.SuccessfullyInvitedAsName' => 'Successfully invited {name}','errno' => 'NF.MS.0022'];
    public const SUCCESSFULLY_LOGGED_IN = ['NF.Message.SuccessfullyLoggedIn' => 'Successfully logged in!','errno' => 'NF.MS.0037'];
    public const SUCCESSFULLY_REGISTERED = ['NF.Message.SuccessfullyRegistered' => 'You have successfully registered, please check your email/sms for email validation!','errno' => 'NF.MS.0042'];
    public const SUCCESSFULLY_REGISTERED_AS_NAME = ['NF.Message.SuccessfullyRegisteredAsName' => 'Successfully registered as {name}','errno' => 'NF.MS.0021'];
    public const SUCCESSFULLY_REGISTERED_S_M_S_VALIDATE_PHONE = ['NF.Message.SuccessfullyRegisteredSMSValidatePhone' => 'You have successfully registered, please click on {url} to validate you phone number!','errno' => 'NF.MS.0043'];
    public const SUCCESSFULLY_SIGNED_IN = ['NF.Message.SuccessfullySignedIn' => 'You have successfully signed in!','errno' => 'NF.MS.0004'];
    public const TOKEN_EXPIRED = ['NF.Message.TokenExpired' => 'Your token is not valid or expired!','errno' => 'NF.MS.0002'];
    public const UNAUTHORIZED = ['NF.Message.Unauthorized' => 'Unauthorized!','errno' => 'NF.MS.0026'];
    public const UNKNOWN_PARAMETER = ['NF.Message.UnknownParameter' => 'Unknown parameter: {parameter}!','errno' => 'NF.MS.0055'];
    public const UNSUBSCRIBE = ['NF.Message.Unsubscribe' => 'unsubscribe','errno' => 'NF.MS.0025'];
    public const USAGE_EXAMPLE = ['NF.Message.UsageExample' => '{name} - usage example.','errno' => 'NF.MS.0056'];
    public const USERS_REGISTER_SIMPLE = ['NF.Message.UsersRegisterSimple' => 'You are receiving this Registration Email because you registered in {config://brand.name.welcome} system.','errno' => 'NF.MS.0007'];
    public const USER_ACCOUNT_IS_ON_HOLD_CALL_SUPPORT_OR_TRY_AGAIN_LATER = ['NF.Message.UserAccountIsOnHoldCallSupportOrTryAgainLater' => 'User account in on hold, please contact support or try logging again later!','errno' => 'NF.MS.0035'];
    public const USER_NOT_FOUND = ['NF.Message.UserNotFound' => 'User not found!','errno' => 'NF.MS.0015'];
    public const VISIT_COUNTER = ['NF.Message.VisitCounter' => '{counter} visit(s)','errno' => 'NF.MS.0038'];
    public const WELCOME_TO_BRAND = ['NF.Message.WelcomeToBrand' => 'Welcome to {config://brand.name.welcome}!','errno' => 'NF.MS.0006'];
    public const YOU_CANNOT_INVITE_YOURSELF = ['NF.Message.YouCannotInviteYourself' => 'You cannot invite yourself!','errno' => 'NF.MS.0019'];
    public const YOU_CAN_NOW_SIGN_IN_INTO_YOUR_ACCOUNT = ['NF.Message.YouCanNowSignInIntoYourAccount' => 'You can now sign in into your account. {signin}.','errno' => 'NF.MS.0051'];
}
