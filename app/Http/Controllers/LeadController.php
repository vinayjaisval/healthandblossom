<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FacebookCapiService;

class LeadController extends Controller
{
    public function submitLead(Request $request, FacebookCapiService $facebook)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'nullable|string',
        ]);

        // Prepare Facebook Event Data
        $eventData = [
            'event_name' => 'Lead',
            'event_time' => time(),
            'action_source' => 'website',
            'event_source_url' => $request->headers->get('referer'),
            'user_data' => [
                'em' => [hash('sha256', $request->email)],
                'ph' => $request->phone ? [hash('sha256', $request->phone)] : null,
                'client_user_agent' => $request->userAgent(),
                'fbc' => $request->cookie('_fbc'),
                'fbp' => $request->cookie('_fbp'),
            ],
            'custom_data' => [
                'value' => 0,
                'currency' => 'USD'
            ],
        ];

        // Send Event to Facebook
        $response = $facebook->sendEvent($eventData);

        return response()->json([
            'message' => 'Lead submitted successfully',
            'facebook_response' => $response
        ]);
    }
  
   public function track(Request $request, FacebookCapiService $facebook)
    {
     
        $validated = $request->validate([
            'event' => 'required|string',
            'url' => 'required|url',
            'timestamp' => 'required|integer',
            'user_agent' => 'nullable|string',
           // 'fbc' => 'nullable|string',
            //'fbp' => 'nullable|string',
        ]);
        // Optionally send to Facebook CAPI
        $eventData = [
            'event_name' => $validated['event'],
            'event_time' => $validated['timestamp'],
            'action_source' => 'website',
            'event_source_url' => $validated['url'],
            'user_data' => [
                'client_user_agent' => $validated['user_agent'],
                'fbc' => $request->fbp,
                'fbp' => $request->fbp,
            ],
        ];
     

        // Send to Facebook
        $facebookResponse = $facebook->sendEvent($eventData);

        // Log or save to DB (optional)
         ClickTrack::create($validated);

        return response()->json([
            'message' => 'Click tracked',
            'facebook_response' => $facebookResponse
        ]);
    }
}
