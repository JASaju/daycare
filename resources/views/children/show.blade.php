@extends("layouts.app")
@section("title", $child->name)
@section("content")
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>{{ $child->name }} <small class="text-muted fs-6">({{ $child->age }} yrs)</small></h3>
        <a href="{{ route("children.index") }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card p-3 mb-3">
                @if($child->photo_url)
                    <img src="{{ $child->photo_url }}" alt="{{ $child->name }}"
                         class="rounded mb-3" loading="lazy" style="width:140px;height:140px;object-fit:cover;">
                @endif

                <h6>Profile</h6>
                <p class="mb-1"><strong>Parent/Employee:</strong> {{ $child->employee->name ?? "-" }}</p>
                <p class="mb-1"><strong>Date of Birth:</strong> {{ $child->date_of_birth->format("d M Y") }}</p>
                <p class="mb-1"><strong>Gender:</strong> {{ ucfirst($child->gender ?? "-") }}</p>
                <p class="mb-1"><strong>Status:</strong> {{ ucfirst($child->status) }}</p>
                <p class="mb-0"><strong>Notes:</strong> {{ $child->notes ?: "-" }}</p>
            </div>

            <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Health Record</h6>
                    @if(in_array(auth()->user()->role, ["admin","staff"]))
                        <a href="{{ route("health.edit", $child) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                    @endif
                </div>
                @if($child->healthRecord)
                    <p class="mb-1"><strong>Blood Group:</strong> {{ $child->healthRecord->blood_group ?: "-" }}</p>
                    <p class="mb-1"><strong>Allergies:</strong> {{ $child->healthRecord->allergies ?: "None recorded" }}</p>
                    <p class="mb-1"><strong>Medications:</strong> {{ $child->healthRecord->medications ?: "None recorded" }}</p>
                    <p class="mb-1"><strong>Medical Conditions:</strong> {{ $child->healthRecord->medical_conditions ?: "None recorded" }}</p>
                    <p class="mb-1"><strong>Doctor:</strong> {{ $child->healthRecord->doctor_name ?: "-" }} {{ $child->healthRecord->doctor_phone }}</p>
                @else
                    <p class="text-muted mb-0">No health record yet.</p>
                @endif
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Emergency Contacts</h6>
                </div>
                @forelse($child->emergencyContacts as $contact)
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <div>{{ $contact->name }} ({{ $contact->relation }}) — {{ $contact->phone }}</div>
                        @if(in_array(auth()->user()->role, ["admin","staff"]))
                            <form action="{{ route("emergency-contacts.destroy", [$child, $contact]) }}" method="POST"
                                  onsubmit="return confirm('Remove?')">
                                @csrf @method("DELETE")
                                <button class="btn btn-sm btn-outline-danger">Remove</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-muted">No emergency contacts added.</p>
                @endforelse

                @if(in_array(auth()->user()->role, ["admin","staff"]))
                    <form method="POST" action="{{ route("emergency-contacts.store", $child) }}" class="row g-2 mt-2">
                        @csrf
                        <div class="col-6"><input type="text" name="name" class="form-control form-control-sm" placeholder="Name" required></div>
                        <div class="col-6"><input type="text" name="relation" class="form-control form-control-sm" placeholder="Relation" required></div>
                        <div class="col-6"><input type="text" name="phone" class="form-control form-control-sm" placeholder="Phone" required></div>
                        <div class="col-6"><input type="text" name="alternate_phone" class="form-control form-control-sm" placeholder="Alt. Phone"></div>
                        <div class="col-12"><button class="btn btn-sm btn-primary">Add Contact</button></div>
                    </form>
                @endif
            </div>

            <div class="card p-3">
                <h6>Recent Attendance</h6>
                <table class="table table-sm mb-0">
                    <thead><tr><th>Date</th><th>Check-in</th><th>Check-out</th></tr></thead>
                    <tbody>
                    @forelse($child->attendances as $a)
                        <tr>
                            <td>{{ $a->date->format("d M Y") }}</td>
                            <td>{{ $a->check_in ? \Carbon\Carbon::parse($a->check_in, "UTC")->setTimezone("Asia/Dhaka")->format("g:i A") : "-" }}</td>
                            <td>{{ $a->check_out ? \Carbon\Carbon::parse($a->check_out, "UTC")->setTimezone("Asia/Dhaka")->format("g:i A") : "-" }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted text-center">No attendance recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
