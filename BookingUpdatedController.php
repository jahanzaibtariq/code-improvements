<?php

namespace DTApi\Http\Controllers;

use DTApi\Models\Job;
use DTApi\Http\Requests;
use DTApi\Models\Distance;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

class BookingController extends Controller
{
    /**
     * @var BookingRepository
     */
    protected $repository;

    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository The booking repository dependency injected via constructor
     */
    public function __construct(BookingRepository $bookingRepository)
    {
        $this->repository = $bookingRepository;
    }

    /**
     * Get the list of bookings based on the request.
     */
    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobs($user_id);
        } elseif ($request->__authenticatedUser->user_type == env('ADMIN_ROLE_ID') || $request->__authenticatedUser->user_type == env('SUPERADMIN_ROLE_ID')) {
            $response = $this->repository->getAll($request);
        }

        return response($response);
    }

    /**
     * Retrieve a specific booking by its ID.
     */
    public function show($id)
    {
        $job = $this->repository->with('translatorJobRel.user')->find($id);

        return response($job);
    }

    /**
     * Store a new booking.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->store($request->__authenticatedUser, $data);

        return response($response);
    }

    /**
     * Update a booking by its ID.
     */
    public function update($id, Request $request)
    {
        $data = $request->all();
        $cuser = $request->__authenticatedUser;
        $response = $this->repository->updateJob($id, array_except($data, ['_token', 'submit']), $cuser);

        return response($response);
    }

    /**
     * Store an immediate job email.
     */
    public function immediateJobEmail(Request $request)
    {
        $adminSenderEmail = config('app.adminemail');
        $data = $request->all();
        $response = $this->repository->storeJobEmail($data);

        return response($response);
    }

    /**
     * Get the history of bookings for a user.
     */
    public function getHistory(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->repository->getUsersJobsHistory($user_id, $request);
            return response($response);
        }

        return null;
    }

    /**
     * Accept a job by its ID.
     */
    public function acceptJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJob($data, $user);

        return response($response);
    }

    /**
     * Accept a job with a specific ID.
     */
    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;
        $response = $this->repository->acceptJobWithId($data, $user);

        return response($response);
    }

    /**
     * Cancel a job.
     */
    public function cancelJob(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->cancelJobAjax($data, $user);

        return response($response);
    }

    /**
     * End a job.
     */
    public function endJob(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->endJob($data);

        return response($response);
    }

    /**
     * Mark a job as "customer not called".
     */
    public function customerNotCall(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->customerNotCall($data);

        return response($response);
    }

    /**
     * Get potential jobs for a user.
     */
    public function getPotentialJobs(Request $request)
    {
        $data = $request->all();
        $user = $request->__authenticatedUser;
        $response = $this->repository->getPotentialJobs($user);

        return response($response);
    }

    /**
     * Update the distance and time for a job.
     */
    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = $data['distance'] ?? "";
        $time = $data['time'] ?? "";
        $jobid = $data['jobid'] ?? "";
        $session = $data['session_time'] ?? "";

        $flagged = $data['flagged'] == 'true' ? 'yes' : 'no';
        $manually_handled = $data['manually_handled'] == 'true' ? 'yes' : 'no';
        $by_admin = $data['by_admin'] == 'true' ? 'yes' : 'no';
        $admincomment = $data['admincomment'] ?? "";

        if ($time || $distance) {
            Distance::where('job_id', '=', $jobid)->update(['distance' => $distance, 'time' => $time]);
        }

        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            Job::where('id', '=', $jobid)->update([
                'admin_comments' => $admincomment,
                'flagged' => $flagged,
                'session_time' => $session,
                'manually_handled' => $manually_handled,
                'by_admin' => $by_admin
            ]);
        }

        return response('Record updated!');
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }
}