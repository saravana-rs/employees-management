<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container-main {
            padding: 30px 0;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: none;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .btn-add {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 25px;
            transition: all 0.3s;
        }
        .btn-add:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
        }
        .employee-logo {
            width: 100px;
            height: 50px;
            object-fit: cover;
            border: 2px solid #667eea;
        }
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
        }
        .preview-container {
            text-align: center;
            margin: 10px 0;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            margin-top: 10px;
        }
        .remove-image-btn {
            margin-top: 10px;
        }
        .loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .loading-overlay.show {
            display: flex;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <div class="container container-main">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h2><i class="fas fa-users"></i> Employee Management System</h2>
                    <button class="btn btn-add" onclick="openAddModal()">
                        <i class="fas fa-plus"></i> Add Employee
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="alertContainer"></div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee Name</th>
                                <th>Employee Email</th>
                                <th>Employee Logo</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employeeTableBody">
                            <!-- Data will be loaded here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Employee Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Add Employee</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="employeeForm" enctype="multipart/form-data">
                        <input type="hidden" id="employeeId" name="employeeId">
                        <input type="hidden" id="existingLogo" name="existingLogo">
                        
                        <div class="mb-3">
                            <label for="employeeName" class="form-label">Employee Name *</label>
                            <input type="text" class="form-control" id="employeeName" name="employeeName" required>
                            <div class="error" id="nameError"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="employeeEmail" class="form-label">Employee Email *</label>
                            <input type="email" class="form-control" id="employeeEmail" name="employeeEmail" required>
                            <div class="error" id="emailError"></div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="employeeLogo" class="form-label">Employee Logo (Max 5MB)</label>
                            <input type="file" class="form-control" id="employeeLogo" name="employeeLogo" accept="image/*">
                            <div class="error" id="logoError"></div>
                            
                            <div class="preview-container" id="previewContainer" style="display: none;">
                                <img id="imagePreview" class="preview-image" src="" alt="Preview">
                                <input type="hidden" class="form-control" id="image_removed" name="image_removed" value="0">
                                <br>
                                <button type="button" class="btn btn-danger btn-sm remove-image-btn" onclick="removeImage()">
                                    <i class="fas fa-trash"></i> Remove Image
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveEmployee()">Save Employee</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Employee Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <div id="previewContent">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" onclick="downloadPDF()">
                        <i class="fas fa-download"></i> Download PDF
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    
    
<script>
let currentEmployeeId = null;
let isEditMode = false;

$(document).ready(function() {
    loadEmployees();
    setupValidation();
    $('#employeeLogo').change(function() { previewImage(this); });
});

function setupValidation() {
    $.validator.addMethod("filesize", function(value, element, param) {
        if (!element.files.length) return true;
        return element.files[0].size <= param;
    }, "File size must be less than 5MB");

    $('#employeeForm').validate({
        rules: {
            name: { required: true, minlength: 2, maxlength: 50 },
            email: { required: true, email: true },
            logo: { filesize: 5242880 }
        },
        errorPlacement: function(error, element) {
            if(element.attr("name")=="name") $("#nameError").html(error);
            else if(element.attr("name")=="email") $("#emailError").html(error);
            else if(element.attr("name")=="logo") $("#logoError").html(error);
        }
    });
}

function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if(file.size > 5242880){ $('#logoError').html('File size must be less than 5MB'); input.value=''; $('#previewContainer').hide(); return; }
        if(!file.type.match('image.*')){ $('#logoError').html('Invalid image'); input.value=''; $('#previewContainer').hide(); return; }

        $('#logoError').html('');
        const reader = new FileReader();
        reader.onload = e => { $('#imagePreview').attr('src', e.target.result); $('#previewContainer').show(); }
        reader.readAsDataURL(file);
    }
}

function removeImage() {

    
     const existingLogo = $('#existingLogo').val(); 
    if(existingLogo) {
        $('#image_removed').val('');
        $('#image_removed').val(existingLogo); 
    }
    $('#employeeLogo').val('');
    $('#imagePreview').attr('src','');
    $('#previewContainer').hide();
    $('#existingLogo').val('');
}

function openAddModal() {
    isEditMode = false;
    $('#modalTitle').text('Add Employee');
    $('#employeeForm')[0].reset();
    $('#employeeId').val('');
    $('#existingLogo').val('');
    $('#previewContainer').hide();
    $('.error').html('');
    $('#employeeModal').modal('show');
}

function openEditModal(id) {
    isEditMode = true;
    currentEmployeeId = id;
    $('#modalTitle').text('Edit Employee');
    $('.error').html('');
    showLoading();
    $.getJSON('get_employee.php', {id}, function(res){
        hideLoading();
        if(res.success){
            $('#employeeId').val(res.data.id);
            $('#employeeName').val(res.data.name);
            $('#employeeEmail').val(res.data.email);
            $('#existingLogo').val(res.data.logo);
            if(res.data.logo){ $('#imagePreview').attr('src','uploads/'+res.data.logo); $('#previewContainer').show(); }
            else { $('#previewContainer').hide(); }
            $('#employeeModal').modal('show');
        } else { showAlert('danger', res.message); }
    }).fail(()=>{ hideLoading(); showAlert('danger','Error loading employee'); });
}

function saveEmployee() {
    if(!$('#employeeForm').valid()) return;

    const formData = new FormData();
    formData.append('id', $('#employeeId').val());
    formData.append('name', $('#employeeName').val());
    formData.append('email', $('#employeeEmail').val());
    formData.append('existingLogo', $('#existingLogo').val());
    formData.append('image_removed', $('#image_removed').val());
    const logoFile = $('#employeeLogo')[0].files[0];
    if(logoFile) formData.append('logo', logoFile);

    showLoading();
    $.ajax({
        url: 'save_employee.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
             hideLoading();
             $('.error').html(''); // Clear previous errors
             
             if (response.success) {
                 $('#employeeModal').modal('hide');
                 showAlert('success', response.message);
                 loadEmployees();
             } else {
                 // Field-specific error handling
                 if (response.field === 'email') {
                     $('#emailError').html(response.message);
                 } else if (response.field === 'name') {
                     $('#nameError').html(response.message);
                 } else {
                     // General alert
                     showAlert('danger', response.message);
                 }
             }
        },
        error: ()=>{ hideLoading(); showAlert('danger','Error saving employee'); }
    });
}

function deleteEmployee(id){
    if(confirm('Are you sure to delete this employee?')){
        showLoading();
        $.post('delete_employee.php',{id},function(res){
            hideLoading();
            if(res.success){ showAlert('success',res.message); loadEmployees(); }
            else showAlert('danger',res.message);
        },'json').fail(()=>{ hideLoading(); showAlert('danger','Error deleting'); });
    }
}

function previewEmployee(id){
    currentEmployeeId = id;
    showLoading();
    $.getJSON('get_employee.php',{id}, function(res){
        hideLoading();
        if(res.success){
            let logoHtml = res.data.logo ? `<img src="uploads/${res.data.logo}" class="img-fluid rounded" style="max-width:200px;">` : '<p>No logo available</p>';
            $('#previewContent').html(`<h4>${res.data.name}</h4><p><strong>Email:</strong> ${res.data.email}</p><div class="mt-3">${logoHtml}</div>`);
            $('#previewModal').modal('show');
        } else showAlert('danger',res.message);
    }).fail(()=>{ hideLoading(); showAlert('danger','Error loading preview'); });
}

function downloadPDF(){ if(currentEmployeeId){ showLoading(); window.location.href='generate_pdf.php?id='+currentEmployeeId; setTimeout(hideLoading,2000); } }

function loadEmployees(){
    showLoading();
    $.getJSON('get_employees.php', function(res){
        hideLoading();
        let html = '';
        if(res.success && res.data.length>0){
            res.data.forEach(emp=>{
                let logoHtml = emp.logo ? `<img src="uploads/${emp.logo}" class="employee-logo">` : '<i class="fas fa-user-circle fa-2x text-muted"></i>';
                html+=`<tr>
                    <td>${emp.id}</td><td>${emp.name}</td><td>${emp.email}</td><td>${logoHtml}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="openEditModal(${emp.id})"><i class="fas fa-edit"></i> Edit</button>
                        <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${emp.id})"><i class="fas fa-trash"></i> Delete</button>
                        <button class="btn btn-sm btn-info" onclick="previewEmployee(${emp.id})"><i class="fas fa-eye"></i> Preview</button>
                    </td>
                </tr>`;
            });
        } else html='<tr><td colspan="5" class="text-center">No employees found</td></tr>';
        $('#employeeTableBody').html(html);
    }).fail(()=>{ hideLoading(); showAlert('danger','Error loading employees'); });
}

function showAlert(type,message){
    $('#alertContainer').html(`<div class="alert alert-${type} alert-dismissible fade show" role="alert">${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>`);
    setTimeout(()=>{$('.alert').fadeOut();},5000);
}

function showLoading(){ $('#loadingOverlay').addClass('show'); }
function hideLoading(){ $('#loadingOverlay').removeClass('show'); }
</script>


</body>
</html>