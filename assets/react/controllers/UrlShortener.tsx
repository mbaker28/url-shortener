import { useState } from "react";
import { useForm } from "react-hook-form";
import "../../styles/button.css";

export default function UrlShortener() { 
    const [status, setStatus] = useState("");
    const [isPending, setPending] = useState(false);
    const { register, handleSubmit } = useForm<{ url: string }>();

    const onSubmit = async (data: { url: string }) => {
        const formData = new FormData();

        formData.set('url', data.url);

        try {
            setPending(true);
            setStatus('');
            const res = await fetch('/url-shortener/create', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                },
                body: formData
            });

            const json: { url: string, shortCode: string } = await res.json();
            setStatus(`Shortened URL: <a href="//${window.location.host}/${json.shortCode}" target="_blank">${window.location.host}/${json.shortCode}</a>`);
        } catch (e) {
            setStatus(e as string);
            console.log(e);
        } finally {
            setPending(false);
        }
    };

    return (
        <form onSubmit={handleSubmit(onSubmit)} className="mb-12 w-full">
            <div className="mb-5">
                <label
                    htmlFor="url"
                    className="mb-3 block font-medium"
                >
                    Enter a URL:
                </label>
                <input
                    type="text"
                    placeholder="https://example.com"
                    required
                    className="w-full rounded-md border border-gray-300 bg-white py-3 px-6 font-medium text-gray-700 outline-none focus:border-purple-500 focus:shadow-md"
                    {...register('url', { required: true })}
                />
            </div>
            <div className="mb-5">
                <button className={`${isPending ? 'loading' : ''} button hover:bg-purple-800 rounded-md bg-purple-500 py-3 px-8 font-semibold text-white outline-none`} disabled={isPending}>
                    <span>Shorten</span>
                </button>
            </div>
            <div dangerouslySetInnerHTML={{ __html: status }}></div>
        </form>
    );
}