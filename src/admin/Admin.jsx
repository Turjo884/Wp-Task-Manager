import React, { useEffect, useState } from "react";
import ReactDOM from "react-dom/client";

const Admin = () => {
    const [tasks, setTasks] = useState([]);
    const [title, setTitle] = useState("");
    const [description, setDescription] = useState("");

    useEffect(() => {
        fetchTasks();
    }, []);

    const fetchTasks = async () => {
        try {
            const response = await fetch(wpTaskManagerPlugin.rest_url);
            const data = await response.json();
            setTasks(data);
        } catch (error) {
            console.error("Error fetching tasks:", error);
        }
    };

    const addTask = async (e) => {
        e.preventDefault();
        const taskData = { title, description };

        try {
            const response = await fetch(wpTaskManagerPlugin.rest_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpTaskManagerPlugin.nonce
                },
                body: JSON.stringify(taskData)
            });

            const data = await response.json();
            if (data.success) {
                fetchTasks();
                setTitle('');
                setDescription('');
            }
        } catch (error) {
            console.error("Error adding task:", error);
        }
    };


    // delete task
    const deleteTask = async (id) => {
        if (!window.confirm("Are you sure you want to delete this task?")) return;
    
        try {
            const response = await fetch(`${wpTaskManagerPlugin.rest_url}/${id}`, {
                method: "DELETE",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": wpTaskManagerPlugin.nonce,
                },
            });
    
            const data = await response.json();
            if (data.success) {
                setTasks(tasks.filter((task) => task.id !== id)); // Remove deleted task from UI
            }
        } catch (error) {
            console.error("Error deleting task:", error);
        }
    };


    // mark as completed
    const markAsComplete = async (id) => {
        try {
            const response = await fetch(`${wpTaskManagerPlugin.rest_url}/${id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-WP-Nonce": wpTaskManagerPlugin.nonce,
                },
                body: JSON.stringify({ status: "completed" }),
            });
    
            const data = await response.json();
            if (data.success) {
                setTasks(tasks.map(task => 
                    task.id === id ? { ...task, status: "completed" } : task
                )); // Instantly update the UI
            }
        } catch (error) {
            console.error("Error updating task:", error);
        }
    };
    
    
    

    return (
        <>
            <h1>Smart Task Manager</h1>
            <form onSubmit={addTask}>
                <input type="text" value={title} onChange={(e) => setTitle(e.target.value)} placeholder="Task Title" required />
                <textarea value={description} onChange={(e) => setDescription(e.target.value)} placeholder="Task Description"></textarea>
                <button type="submit">Add Task</button>
            </form>

            <table>
                <thead>
                    <tr>
                    <th>ID</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Created_at</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {tasks?.map((task) => (
                        <tr key={task.id}>
                            <td>{task.id}</td>
                            <td>{task.title}</td>
                            <td>{task.description}</td>
                            <td>{task.status}</td>
                            <td>{task.created_at}</td>
                            <td>
                                {task.status === "pending" ? (
                                    <button
                                        onClick={() => markAsComplete(task.id)}
                                        style={{ cursor: "pointer", background: "green", color: "white", border: "none", padding: "5px", borderRadius: "3px" }}
                                    >
                                        Mark as Complete
                                    </button>
                                ) : (
                                    <span style={{ color: "green", fontWeight: "bold" }}>✅ Completed</span>
                                )}
                            </td>

                            <td>
                                <button onClick={() => deleteTask(task.id)}>Delete</button>
                            </td>
                        </tr>
                    ))}
                </tbody>

            </table>
        </>
    );
};

const root = document.getElementById("wp-task-manager-plugin-id");
if (root) {
    ReactDOM.createRoot(root).render(<Admin />);
}
