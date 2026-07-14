import React from 'react';
import { InputHTMLAttributes } from 'react';

type InputProps = InputHTMLAttributes<HTMLInputElement> &{
    type?: string,
    label?: string,
    placeholder?: string | number,
    description?: string,
    error?: string,
}

const Input = ({type="text",label,placeholder, description, error}:InputProps) => {
    return (
        <div className={`flex flex-col w-fit`} dir='rtl'>
            <label className={`p-1 text-accent-dark`} htmlFor="input">{label}</label>
            <input id='input' type={type} placeholder={placeholder} 
            className={`w-68 h-10 bg-background border border-border placeholder:text-text-secondary rounded-lg px-4 py-3 outline-none`}/>
            <p className={`text-text-secondary`}>{description}</p>
            <p className={`text-error text-sm`}>{error}</p>
        </div>
    );
}

export default Input;
