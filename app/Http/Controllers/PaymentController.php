<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\UserToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function firstStep(Request $request)
    {
        $event_id = $request->event_id;
        $amount_cents = $request->event_price;

        $userOrderDetails =[
            "auth_token"=>NULL,
            "order_id"=>NULL,
            "merchant_order_id"=>NULL,
            "user_id"=>NULL,
            "event_id" => $event_id
        ];
        $user_token = $request->bearerToken();

        $api = [
            'api_key' =>  env('PAYMOB_API_KEY')
        ];
        $firstFetch = 'https://accept.paymob.com/api/auth/tokens';
        $integrationID = 3801519;

        $response = curl_init($firstFetch);

        curl_setopt_array($response,[
            CURLOPT_URL => $firstFetch,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['content-type:application/json'],
            CURLOPT_POSTFIELDS => json_encode($api),
        ]);

        $result = curl_exec($response);
        $result_obj = json_decode($result, true);
        //return $result_obj;
        $token = $result_obj['token'];

        $userOrderDetails['auth_token']=$token;
        curl_close($response);

        $data = [
            "auth_token"=> $token,
            "delivery_needed" => "false",
            "amount_cents"=> $amount_cents,
            "currency"=> "EGP",
            "items"=> [],
            "merchant_order_id"=>self::bigNumber(),
        ];
        $userOrderDetails['merchant_order_id']=$data["merchant_order_id"];
        $usercurrent = Auth::user();


        $userOrderDetails['user_id']=$usercurrent['id'];
        //return $user_token;
        $order_id =self::secondStep($data);
        $userOrderDetails['order_id']=$order_id;        ;
        $lastPaymentToken = self::thirdStep($userOrderDetails['auth_token'],$userOrderDetails['order_id'],$amount_cents);
        UserToken::create(
            $userOrderDetails
        );
        return response()->json(["token3"=>$lastPaymentToken ],201);
        // return $step2;
        //error_log('firststep',$token);

    }

    public function secondStep($data)
    {

        $secondFetch = 'https://accept.paymob.com/api/ecommerce/orders';
        $response = curl_init($secondFetch);
        curl_setopt_array($response,[
            CURLOPT_URL => $secondFetch,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['content-type:application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $result = curl_exec($response);
        $result_obj = json_decode($result, true);
        //return $result_obj; /////////////////////////////////
        curl_close($response);
        //error_log('second');

        $order_id = $result_obj['id']; // order id
        $token = $data['auth_token']; // auth token from 1

        return $order_id;

        // return $step3;
    }

    public function thirdStep($token , $order_id,$amount_cents)
    {
        $integrationID = 3801519;
        $current_user = Auth::user();
       // error_log(json_encode($current_user['mobile']));
        $data = [
            "auth_token"=> $token,  //auth token from 1
            "amount_cents"=> $amount_cents, ////////////
            "expiration"=> 3600,
            "order_id"=> $order_id,
            "billing_data"=> [
                "apartment"=> "NA",
                "email"=> $current_user['email'],
                "floor"=> "NA",
                "first_name"=> $current_user['first_name'],
                "street"=> "NA",
                "building"=> "NA",
                "phone_number"=> $current_user['mobile'],
                "shipping_method"=> "NA",
                "postal_code"=> "NA",
                "city"=> "NA",
                "country"=> "NA",
                "last_name"=> $current_user['last_name'],
                "state"=> "NA"
            ],
            "currency"=> "EGP",
            "integration_id"=>$integrationID,
            "current_user" => $current_user
        ];

        $thirdFetch = 'https://accept.paymob.com/api/acceptance/payment_keys';
        $response = curl_init($thirdFetch);
        curl_setopt_array($response,[
            CURLOPT_URL => $thirdFetch,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['content-type:application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $result = curl_exec($response);
        $result_obj = json_decode($result, true);
        $token3 = $result_obj['token'];

        //$step4 = self::thirdStep($token,$id);
        curl_close($response);
        return $token3;

    }
    public function paymentVerification(Request $request){
        $order_id = $request->order_id;
        $event_id = $request->event_id;
        $orderDetails = UserToken::where('order_id',$order_id)->firstOrFail();
        $data = [
                "auth_token"=> $orderDetails["auth_token"],
                "merchant_order_id"=> $orderDetails["merchant_order_id"],
                "order_id"=>  $orderDetails["order_id"],
                // TODO => add event id
        ];
        // $thirdFetch =  `https://accept.paymob.com/api/acceptance/transactions/{payment_id}`;
        $thirdFetch ="https://accept.paymobsolutions.com/api/ecommerce/orders/transaction_inquiry";
        $response = curl_init($thirdFetch);
        curl_setopt_array($response,[
            CURLOPT_URL => $thirdFetch,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ['content-type:application/json'],
            CURLOPT_POSTFIELDS => json_encode($data),
        ]);
        $result = curl_exec($response);
        $result_obj = json_decode($result, true);

        $isSuccessed=$result_obj['success'];
        $paidAmount =$result_obj['order']['paid_amount_cents'];

        $eventData = Event::find($event_id);
        $eventPrice = $eventData['price'];


        if($isSuccessed){
            // TODO bussesinnes of register the event

            // TODO , Check The ammount

            return response()->json([$result_obj,'message' => 'Process Completed successfully!','status'=>'success','paidAmount'=>$paidAmount,'eventPrice'=>$eventPrice],200);

            // if($event_price_final == $paidAmount)
            // {
            //     return response()->json([$result_obj,'message' => 'Process Completed successfully!','status'=>'success'],200);
            // }
            // if($event_price_final > $paidAmount)
            // {
            //     return response()->json(['error'=>'The Amount Paid is less than the Ticket Price','status'=>'failed'],401);
            // }
            // if($event_price_final < $paidAmount)
            // {
            //     return response()->json(['error'=>'The Amount Paid is More than the Ticket Price','status'=>'failed'],401);
            // }

        }
        else
        {
            return response()->json(['message'=>'process failed','status'=>'failed'],401);
        }

    }
    public function bigNumber() {
        # prevent the first number from being 0
        $output = rand(1,9);

        for($i=0; $i<16; $i++) {
            $output .= rand(0,9);
        }

        return $output;
    }
}
