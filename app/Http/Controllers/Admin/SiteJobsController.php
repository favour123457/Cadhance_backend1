<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiteJob;
use App\Models\SiteJobStatus;
use Illuminate\Http\Request;

class SiteJobsController extends Controller
{
    public function index()
    {
        $siteJobs = SiteJob::with(['user', 'site_job_status'])->withCount('site_job_applications')->latest()->get();
        return view('admin.site-jobs.index', compact('siteJobs'));
    }

    public function show(SiteJob $siteJob)
    {
        $siteJob->load(['user', 'site_job_status', 'site_job_applications.user']);
        return view('admin.site-jobs.show', compact('siteJob'));
    }

    public function edit(SiteJob $siteJob)
    {
        $statuses = SiteJobStatus::all();
        return view('admin.site-jobs.edit', compact('siteJob', 'statuses'));
    }

    public function update(Request $request, SiteJob $siteJob)
    {
        $request->validate([
            'site_job_status_id' => 'required|exists:site_job_statuses,id',
            'title'              => 'required|string|max:255',
            'description'        => 'required|string',
            'location'           => 'required|string',
            'deadline'           => 'required|string',
            'min_salary'         => 'required|numeric|min:0',
            'max_salary'         => 'required|numeric|min:0',
            'salary_type'        => 'required|string',
            'link'               => 'nullable|string',
            'contact_email'      => 'nullable|email',
        ]);

        $siteJob->update($request->only([
            'site_job_status_id', 'title', 'description', 'location',
            'deadline', 'min_salary', 'max_salary', 'salary_type',
            'link', 'contact_email',
        ]));

        flash()->success('Site job updated successfully.');
        return redirect()->route('site-jobs.index');
    }

    public function destroy(SiteJob $siteJob)
    {
        $siteJob->delete();
        flash()->success('Site job deleted successfully.');
        return redirect()->route('site-jobs.index');
    }
}
