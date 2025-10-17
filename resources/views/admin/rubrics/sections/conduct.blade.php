<div class="rubric-section">
    <h4 class="rubric-heading">V. Good Conduct</h4>

    <div class="table-wrap">
        <table class="manage-table">
            <thead>
                <tr>
                    <th>Conduct Record</th>
                    <th>Status</th>
                    <th>Points</th>
                    <th>Max Points</th>
                    <th>Evidence</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>No Disciplinary Action</td>
                    <td>Clean Record</td>
                    <td>5.0</td>
                    <td>5</td>
                    <td>Good Moral Certificate</td>
                    <td>
                        <button class="btn btn-disable" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>Exemplary Behavior</td>
                    <td>Outstanding</td>
                    <td>5.0</td>
                    <td>5</td>
                    <td>Certificate of Good Conduct</td>
                    <td>
                        <button class="btn btn-disable" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>Positive Peer Relations</td>
                    <td>Excellent</td>
                    <td>4.0</td>
                    <td>5</td>
                    <td>Peer Evaluation Report</td>
                    <td>
                        <button class="btn btn-disable" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>Respectful Behavior</td>
                    <td>Satisfactory</td>
                    <td>4.0</td>
                    <td>5</td>
                    <td>Faculty Recommendation</td>
                    <td>
                        <button class="btn btn-disable" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                <tr>
                    <td>Minor Infraction</td>
                    <td>Warning Issued</td>
                    <td>2.0</td>
                    <td>5</td>
                    <td>Disciplinary Record</td>
                    <td>
                        <button class="btn btn-disable" title="Edit"><i class="fas fa-edit"></i></button>
                        <button class="btn btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <h5>Add New Conduct Record</h5>
        <form>
            <div class="form-row">
                <input type="text" placeholder="Conduct Record" class="form-control">
                <select class="form-control">
                    <option value="">Select Status</option>
                    <option value="outstanding">Outstanding</option>
                    <option value="excellent">Excellent</option>
                    <option value="satisfactory">Satisfactory</option>
                    <option value="clean_record">Clean Record</option>
                    <option value="warning_issued">Warning Issued</option>
                    <option value="minor_infraction">Minor Infraction</option>
                    <option value="major_infraction">Major Infraction</option>
                </select>
                <input type="number" placeholder="Points" class="form-control" step="0.1" min="0" max="5">
                <input type="text" placeholder="Evidence" class="form-control">
                <button class="btn btn-disable" type="button" title="Add"><i class="fas fa-plus"></i></button>
            </div>
        </form>
    </div>
</div>
