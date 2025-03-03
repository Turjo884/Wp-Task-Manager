import React from "react";
import ReactDOM from 'react-dom/client';

const Admin = () => {
    return(
        <>
            <form action="">
                <input type="text" />
                <input type="textarea" />
                <button>Submit</button>
            </form>           
        </>
    )
}

export default Admin;

ReactDOM.createRoot(document.getElementById('wp-task-manager-plugin-id')).render(
    <Admin/>
)