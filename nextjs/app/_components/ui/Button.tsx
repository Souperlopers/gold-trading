import React from 'react';
import { ButtonHTMLAttributes } from 'react';

type ButtonProps = ButtonHTMLAttributes<HTMLButtonElement> &{
    title: string;
}

const Button = ({title, onClick, disabled}:ButtonProps) => {
    return (
        <button type='button' onClick={onClick} disabled={disabled} 
        className={`p-3 w-[272px] h-10 flex justify-center items-center bg-primary hover:bg-primary-hover text-accent-dark rounded-lg border-surface font-medium transition-colors duration-300`}>
            {title}
        </button>
    );
}

export default Button;
