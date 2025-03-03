import React from "react";
import ReactDOM from 'react-dom/client';

const Admin = () => {
    return(
        <>
        <h1>Smart Task Manager</h1>
        <form action="">
            <input type="text" />
            <textarea name="" id=""></textarea>
            <button type="submit">Add Task</button>
        </form> 

         <table>
            <thead>
                <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                </tr>
            </thead>
        </table>  

        <tbody>
            <tr>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tbody>      
        </>
    )
}

export default Admin;

ReactDOM.createRoot(document.getElementById('wp-task-manager-plugin-id')).render(
    <Admin/>
)