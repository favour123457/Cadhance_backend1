<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteJobApplicationResource;
use App\Http\Resources\SiteJobResource;
use App\Models\Notification;
use App\Models\SiteJob;
use App\Models\SiteJobApplication;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class SiteJobController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $jobs = SiteJob::where('site_job_status_id', 1)
            ->where('deadline', '>=', now()->toDateString())
            ->when($search, fn($q) => $q->where('title', 'like', "%$search%"))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(SiteJobResource::collection($jobs));
    }

    public function show($id)
    {
        $job = SiteJob::find($id);

        if (!$job) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Job not found!'
            ], 404);
        }

        return response()->json(new SiteJobResource($job));
    }

    public function myJobs()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $jobs = SiteJob::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(SiteJobResource::collection($jobs));
    }

    public function store(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'contact_email' => 'nullable|email|max:255|required_without:link',
            'location'      => 'nullable|string|max:255',
            'deadline'      => 'nullable|date',
            'min_salary'    => 'nullable|numeric|min:0',
            'max_salary'    => 'nullable|numeric|min:0',
            'salary_type'   => 'nullable|string|max:50',
            'link'          => 'nullable|string|max:255|required_without:contact_email',
        ]);

        $image = '';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('jobs', $fileName, 'r2');
            $image = 'jobs/' . $fileName;
        }

        $job = SiteJob::create([
            'user_id' => $user->id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'deadline' => $request->deadline,
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'salary_type' => $request->salary_type,
            'link' => $request->link,
            'contact_email' => $request->contact_email,
            'image' => $image,
            'site_job_status_id' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Job posted successfully!',
            'job' => new SiteJobResource($job),
        ]);
    }

    public function update(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $job_id = $request->job_id;
        $job = SiteJob::find($job_id);

        if (!$job || $job->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid job!'
            ], 400);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('jobs', $fileName, 'r2');
            $job->update(['image' => 'jobs/' . $fileName]);
        }

        $job->update($request->except(['job_id', 'user_id', 'image']));

        return response()->json([
            'status' => 'success',
            'message' => 'Job updated successfully!',
            'job' => new SiteJobResource($job->fresh()),
        ]);
    }

    public function destroy(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $job_id = $request->job_id;
        $job = SiteJob::find($job_id);

        if (!$job || $job->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid job!'
            ], 400);
        }

        SiteJobApplication::where('site_job_id', $job->id)->delete();
        $job->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Job deleted successfully!',
        ]);
    }

    public function apply(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $job_id = $request->job_id;
        $job = SiteJob::find($job_id);

        if (!$job) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid job!'
            ], 400);
        }

        $exists = SiteJobApplication::where('user_id', $user->id)->where('site_job_id', $job_id)->first();
        if ($exists) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Already applied for this job!'
            ], 400);
        }

        $application = SiteJobApplication::create([
            'site_job_id' => $job_id,
            'user_id' => $user->id,
            'application_date' => now(),
            'status' => 'applied',
        ]);

        Notification::create([
            'user_id' => $job->user_id,
            'title' => 'New Job Application',
            'message' => $user->first_name . ' ' . $user->last_name . ' applied for "' . $job->title . '"!',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Application submitted successfully!',
            'application' => new SiteJobApplicationResource($application),
        ]);
    }

    public function withdrawApplication(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $application_id = $request->application_id;
        $application = SiteJobApplication::find($application_id);

        if (!$application || $application->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid application!'
            ], 400);
        }

        $application->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Application withdrawn successfully!',
        ]);
    }

    public function myApplications()
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $applications = SiteJobApplication::where('user_id', $user->id)->get();

        return response()->json(SiteJobApplicationResource::collection($applications));
    }

    public function jobApplications($id)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $job = SiteJob::find($id);

        if (!$job || $job->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid job!'
            ], 403);
        }

        $applications = SiteJobApplication::where('site_job_id', $id)->get();

        return response()->json(SiteJobApplicationResource::collection($applications));
    }

    public function updateApplicationStatus(Request $request)
    {
        $token = JWTAuth::parseToken();
        $user = $token->authenticate();

        $application_id = $request->application_id;
        $status = $request->status;

        $application = SiteJobApplication::find($application_id);

        if (!$application) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid application!'
            ], 400);
        }

        $job = SiteJob::find($application->site_job_id);

        if (!$job || $job->user_id != $user->id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized access!'
            ], 403);
        }

        $application->update(['status' => $status]);

        Notification::create([
            'user_id' => $application->user_id,
            'title' => 'Job Application Update',
            'message' => 'Your application for "' . $job->title . '" has been ' . $status . '!',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Application status updated successfully!',
        ]);
    }
}
