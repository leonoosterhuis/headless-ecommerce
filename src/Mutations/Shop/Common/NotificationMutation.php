<?php

namespace Webkul\GraphQLAPI\Mutations\Shop\Common;

use Exception;
use Webkul\Shop\Http\Controllers\Controller;
use Webkul\GraphQLAPI\Repositories\NotificationRepository;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class NotificationMutation extends Controller
{
    /**
     * Contains current guard
     *
     * @var array
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Webkul\GraphQLAPI\Repositories\NotificationRepository  $notificationRepository
     * @return void
     */
    public function __construct(protected NotificationRepository $notificationRepository)
    {
    }

    /**
     * Send notificaiton to the app user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function send($rootValue, array $args, GraphQLContext $context)
    {
        $data = $args['input'];
        
        $validator = \Validator::make($data, [
            'id'       => 'required|numeric',
        ]);
        
        if ($validator->fails()) {
            throw new Exception($validator->messages());
        }

        try {
            $notification = $this->notificationRepository->findOrFail($data['id']);

            $result = $this->notificationRepository->sendGCM($notification);

            if (isset($result->message_id)) {
                return [
                    'success'       => trans('bagisto_graphql::app.admin.alert.sended-successfully', ['name' => 'Notification']),
                    'status'        => true,
                    'message_id'    => $result->message_id
                ];
            } elseif (isset($result->error)) {
                return [
                    'success'       => $result->error,
                    'status'        => false,
                    'message_id'    => null
                ];
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}