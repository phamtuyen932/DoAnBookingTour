<?php

namespace App\Http\Controllers;

use App\Jobs\SendMailBookingJob;
use App\Libraries\MomoPayment;
use App\Libraries\Notification;
use App\Libraries\Utilities;
use App\Models\Booking;
use App\Models\Contact;
use App\Models\Destination;
use App\Models\Review;
use App\Models\Tour;
use App\Models\Type;
use App\Services\ClientService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    protected $notification;
    protected $clientService;

    public function __construct(Notification $notification, ClientService $clientService)
    {
        $this->notification = $notification;
        $this->clientService = $clientService;
    }

    /**
     * Display a Homepage.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index(Destination $destination, Type $type, Tour $tour)
    {
        $destinations = $destination->getByStatus(1, 5);
        $types = $type->getByStatus(1, 3);
        $trendingTours = $tour->getByTrending(true, 3);
        $tours = $tour->getByStatus(1, 3);

        return view('index', compact(['destinations', 'trendingTours', 'types', 'tours']));
    }

    /**
     * Show list tour of destination.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function listTour(Request $request, $slug, Type $type)
    {
        $types = $type->getOrderByTitle();
        $tours = $this->clientService->getListTour($request, $slug);
        $filterDuration = $request->filter_duration ?? [];
        $filterType = $request->filter_type ?? [];
        $destination = Destination::where('slug', $slug)->first();

        return view('list_tour', compact(['tours', 'types', 'filterDuration', 'filterType', 'destination']));
    }

    /**
     * Show tour detail.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function showTour(Request $request, $slug, Tour $tourModel)
    {
        $tour = $tourModel->getTourBySlug($slug);
        $tour->faqs = $tour->faqs(true)->get();
        $tour->reviews = $tour->reviews(true)->get();
        $relateTours = $tourModel->getRelated($tour);
        $reviews = $tour->reviews(true)->paginate(8);
        $rateReview = Utilities::calculatorRateReView($tour->reviews);

        return view('tour_detail', compact(['tour', 'relateTours', 'reviews', 'rateReview']));
    }

    /**
     * Show booking page.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function booking(Request $request, $slug, Tour $tourModel)
    {
        $tour = $tourModel->getTourBySlug($slug);
        $people = $request->people;
        $departureTime = $request->departure_time;
        $listRooms = $request->room;
        $booking = null;

        return view('booking', compact(['tour', 'people', 'departureTime', 'listRooms', 'booking']));
    }

    /**
     * Display contact page.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function contact()
    {
        return view('contact');
    }

    /**
     * Store contact
     *
     * @param Request $request
     * @param Contact $contact
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeContact(Request $request, Contact $contact)
    {
        $request->validate($contact->rules(), [], [
            'name' => 't??n',
            'email' => 'email',
            'phone' => 's??? ??i???n tho???i',
            'message' => 'n???i dung',
        ]);
        try {
            $contact->saveData($request);
            $this->notification->setMessage('G???i ph???n h???i th??nh c??ng', Notification::SUCCESS);

            return redirect()->route('index')->with($this->notification->getMessage());
        } catch (Exception $e) {
            $this->notification->setMessage('G???i ph???n h???i th???t b???i', Notification::ERROR);

            return back()
                ->with('exception', $e->getMessage())
                ->with($this->notification->getMessage())
                ->withInput();
        }
    }

    /**
     * Display search page.
     *
     * @param Request $request
     * @param Type $type
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function search(Request $request, Type $type)
    {
        $types = $type->getOrderByTitle();
        $tours = $this->clientService->searchTour($request);
        $filterDuration = $request->filter_duration ?? [];
        $filterType = $request->filter_type ?? [];

        return view('search', compact(['tours', 'types', 'filterDuration', 'filterType']));
    }

    /**
     * Display destination page.
     *
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function destination()
    {
        $destinations = $this->clientService->listDestination();

        return view('destination', compact(['destinations']));
    }

    /**
     * Store review
     *
     * @param Request $request
     * @param $slug
     * @param Review $review
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeReview(Request $request, $slug, Review $review)
    {
        $request->validate($review->rules());
        try {
            $tour = Tour::where('slug', $slug)->firstOrFail();
            $review->saveData($request, $tour);
            $this->notification->setMessage('????nh gi?? ???? ???????c g???i th??nh c??ng', Notification::SUCCESS);

            return back()->with($this->notification->getMessage());
        } catch (Exception $e) {
            $this->notification->setMessage('????nh gi?? g???i kh??ng th??nh c??ng', Notification::ERROR);

            return back()
                ->with('exception', $e->getMessage())
                ->with($this->notification->getMessage())
                ->withInput();
        }
    }

    public function thank()
    {
        return view('admin.bookings.thank');
    }

    /**
     * Store booking
     *
     * @param Request $request
     * @param $slug
     * @param Tour $tourModel
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function storeBooking(Request $request, $slug, Tour $tourModel)
    {
        $tour = $tourModel->getTourBySlug($slug);
        $request->validate($this->clientService->ruleBooking(), [], [
            'first_name' => 't??n',
            'last_name' => 'h???',
            'phone' => '??i???n tho???i',
            'people' => 's?? ng?????i',
            'departure_time' => 'ng??y',
            'payment_method' => 'lo???i thanh to??n',
            'address' => '?????a ch???',
            'city' => 'th??nh ph???',
            'province' => 'huy???n',
            'country' => 'qu???c gia',
            'zipcode' => 'm?? zipcode',
        ]);
        $this->notification->setMessage('?????t tour th??nh c??ng', Notification::SUCCESS);

        DB::beginTransaction();
        try {
            $booking = $this->clientService->storeBooking($request, $tour);
            if ($request->payment_method == PAYMENT_MOMO) {
                $orderIDMomo = 'MM' . time();
                $booking->invoice_no = $orderIDMomo;
                $booking->save();

                $response = MomoPayment::purchase([
                    'ipnUrl' => route('booking.momo.confirm'),
                    'redirectUrl' => route('booking.momo.redirect'),
                    'orderId' => $orderIDMomo,
                    'amount' => strval($booking->total),
                    'orderInfo' => 'Thanh to??n h??a ????n ?????t tour du l???ch c??ng ty Tuy???n Ph???m Travel',
                    'requestId' => $orderIDMomo,
                    'extraData' => '',
                ]);

                if ($response->successful()) {
                    DB::commit();
                    return response()->json([
                        'url' => $response->json('payUrl'),
                        'response' => $response->json(),
                    ]);
                } else {
                    DB::rollBack();
                    $this->notification->setMessage('Serve Momo kh??ng ph???n h???i, vui l??ng th??? l???i sau ho???c ch???n ph????ng th???c thanh to??n kh??c');
                }
            } else {
                DB::commit();
                dispatch(new SendMailBookingJob($booking));
            }
        } catch (Exception $e) {
            DB::rollBack();
            dd($e);
            $this->notification->setMessage('?????t tour kh??ng th??nh c??ng', Notification::ERROR);
        }
        
        return response()->json($this->notification->getMessage());
    }

    /* MOMO */
    public function redirectMomo(Request $request)
    {
        $checkPayment = MomoPayment::completePurchase($request);
        $notification = array(
            'message' => $checkPayment['message'],
            'alert-type' => 'error',
        );
        $booking = Booking::where('invoice_no', $request->orderId)->first();
        if ($booking != null) {
            if ($checkPayment['success']) {
                $booking->is_payment = PAYMENT_PAID;
                $booking->transaction_id = $request->transId;
                $booking->deposit = $booking->total;
                $booking->save();
                $notification = array(
                    'message' => '?????t h??ng th??nh c??ng',
                    'alert-type' => 'success',
                );
                dispatch(new SendMailBookingJob($booking));
            } else {
                $tour = $booking->tour;
                $people = $booking->people;
                $departureTime = $booking->departure_time;
                $roomId = $booking->room_id;
                $numberRoom = $booking->number_room;
                $errorMomo = $notification['message'];

                return view('booking', compact([
                    'tour',
                    'people',
                    'departureTime',
                    'roomId',
                    'numberRoom',
                    'booking',
                    'errorMomo'
                ]));
            }

        } else {
            $notification['message'] = 'M?? h??a ????n kh??ng ????ng';
        }

        return redirect()->route('booking.thank')->with($notification);
    }

    public function confirmMomo(Request $request)
    {
        $booking = Booking::where('invoice_no', $request->orderId)->first();
        if ($booking != null) {
            $booking->is_payment = PAYMENT_PAID;
            $booking->transaction_id = $request->transId;
            $booking->save();
        }
    }

    public function checkRoom(Request $request, $slug)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);
        $tourModel = new Tour();
        $tour = $tourModel->getTourBySlug($slug);
        $offsetDate = ($tour->duration - 1) * -1;
        $startDate = Carbon::parse($request->date)->addDays($offsetDate);
        $endDate = Carbon::parse($request->date);
        $bookings = Booking::with('booking_room')
            ->where('status', '!=', BOOKING_CANCEL)
            ->whereDate('departure_time', '>=', $startDate)
            ->whereDate('departure_time', '<=', $endDate)
            ->where('tour_id', $tour->id)
            ->get();

        $roomAvailable = [];
        foreach ($tour->rooms as $room) {
            $roomAvailable[$room->id] = $room->number;
        }

        foreach ($bookings as $booking) {
            foreach ($booking->booking_room as $bookingRoom) {
                $roomAvailable[$bookingRoom->room_id] -= $bookingRoom->number;
                if ($roomAvailable[$bookingRoom->room_id] < 0) {
                    $roomAvailable[$bookingRoom->room_id] = 0;
                }
            }
        }

        return response()->json([
            'date' => $request->date,
            'room_available' => $roomAvailable,
        ]);
    }
}