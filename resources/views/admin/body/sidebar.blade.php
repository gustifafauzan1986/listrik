        <div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
					<img src="assets/images/logo-icon.png" class="logo-icon" alt="logo icon">
				</div>
				<div>
					<h4 class="logo-text">Listrik
				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
				</div>
			 </div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
                <li>
					<a href="{{route('dashboard')}}">
						<div class="parent-icon"><i class='bx bx-home-alt'></i>
						</div>
						<div class="menu-title">Dashboard</div>
					</a>
				</li>
				<li class="menu-label">UI Elements</li>


                @role('guru|admin')
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-bookmark-heart'></i>
						</div>
						<div class="menu-title">Schedule</div>
					</a>
					<ul>
						<li> <a href="{{route('schedule.index')}}"><i class='bx bx-radio-circle'></i>Lihat</a>
						</li>

					</ul>
				</li>
                @endrole
                @role('admin')
                <li>
					<a href="javascript:;" class="has-arrow">
						<div class="parent-icon"><i class='bx bx-user'></i>
						</div>
						<div class="menu-title">Murid</div>
					</a>
					<ul>
						<li> <a href="{{route('students.index')}}"><i class='bx bx-radio-circle'></i>Lihat</a>
						</li>

					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-repeat"></i>
						</div>
						<div class="menu-title">Hak Akses</div>
					</a>
					<ul>
						<li> <a href="{{route('permissions.index')}}"><i class='bx bx-radio-circle'></i>Permission</a>
						</li>
                        <li> <a href="{{route('roles.index')}}"><i class='bx bx-radio-circle'></i>Role</a>
						</li>

					</ul>
				</li>
                <li class="menu-label">Forms & Tables</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Print</div>
					</a>
					<ul>
						<li> <a href="{{url('/print-all-cards')}}"><i class='bx bx-radio-circle'></i>Kartu</a>
						<li> <a href="{{url('/face/register')}}"><i class='bx bx-radio-circle'></i>Scan Wajah</a>
						</li>

					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class="bx bx-grid-alt"></i>
						</div>
						<div class="menu-title">GTK</div>
					</a>
					<ul>
						<li> <a href="{{route('users.import')}}"><i class='bx bx-radio-circle'></i>Impor Guru</a>
						</li>
					</ul>
				</li>

                @endrole
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"> <i class="bx bx-donate-blood"></i>
						</div>
						<div class="menu-title">Laporan</div>
					</a>
					<ul>
						<li> <a href="{{route('report.index')}}"><i class='bx bx-radio-circle'></i>Lihat</a>
						</li>

					</ul>
				</li>
			</ul>
			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
